import argparse
from datetime import datetime, timezone
import hashlib
import hmac
import json
from pathlib import Path
import secrets
import subprocess
import sys
import urllib.error
import urllib.request


def read_environment(path):
    values = {}
    for line in path.read_text().splitlines():
        if '=' in line and not line.lstrip().startswith('#'):
            name, value = line.split('=', 1)
            values[name] = value.strip().strip('"').strip("'")
    return values


class Api:
    def __init__(self, base):
        self.base = base.rstrip('/')
        self.token = None
        self.requests = 0

    def request(self, method, path, payload=None, expected=200, signature=None, authenticated=True):
        body = json.dumps(payload, separators=(',', ':')).encode() if payload is not None else None
        headers = {'Accept': 'application/json', 'Content-Type': 'application/json'}
        if authenticated and self.token:
            headers['Authorization'] = 'Bearer ' + self.token
        if signature:
            headers['X-Webhook-Signature'] = signature
        request = urllib.request.Request(self.base + path, data=body, headers=headers, method=method)
        try:
            with urllib.request.urlopen(request, timeout=30) as response:
                status, result = response.status, response.read()
        except urllib.error.HTTPError as error:
            status, result = error.code, error.read()
        self.requests += 1
        if status != expected:
            raise RuntimeError(f'{method} {path}: HTTP {status}, esperado {expected}')
        return json.loads(result) if result else {}


def cpf():
    digits = [secrets.randbelow(10) for _ in range(9)]
    for length in (9, 10):
        remainder = sum(value * (length + 1 - index) for index, value in enumerate(digits)) * 10 % 11
        digits.append(0 if remainder == 10 else remainder)
    return ''.join(map(str, digits))


def require(condition, message):
    if not condition:
        raise RuntimeError(message)


def validate(args):
    environment = read_environment(args.env_file)
    for name in ('ADMIN_EMAIL', 'ADMIN_PASSWORD', 'SERVICE_ORDER_WEBHOOK_SECRET'):
        require(bool(environment.get(name)), f'{name} deve estar definido no arquivo privado')
    api = Api(args.url)
    api.token = api.request('POST', '/api/admin/auth/login', {
        'email': environment['ADMIN_EMAIL'], 'password': environment['ADMIN_PASSWORD'],
    }, authenticated=False)['access_token']
    run_id = secrets.token_hex(4)
    cases = [('none', False, False, 'approved'), ('email', True, False, 'rejected'),
             ('phone', False, True, 'approved'), ('both', True, True, 'rejected')]
    results = []
    mail_before = Api(args.mailpit_url).request('GET', '/api/v1/messages')['total']
    sms_before = sms_count(args.compose_dir)
    orders = []
    for index, (name, email, phone, decision) in enumerate(cases):
        case_mail_before = Api(args.mailpit_url).request('GET', '/api/v1/messages')['total']
        case_sms_before = sms_count(args.compose_dir)
        document = cpf()
        contacts = {'email': f'{name}-{run_id}@example.test' if email else None,
                    'phone': '+5511999990000' if phone else None}
        customer = api.request('POST', '/api/admin/customers', {
            'name': 'Validacao ' + name + ' ' + run_id, 'document': document, **contacts,
        }, expected=201)['data']
        plate = ''.join(secrets.choice('ABCDEFGHIJKLMNOPQRSTUVWXYZ') for _ in range(3))
        plate += str(secrets.randbelow(10)) + secrets.choice('ABCDEFGHIJKLMNOPQRSTUVWXYZ')
        plate += ''.join(str(secrets.randbelow(10)) for _ in range(2))
        vehicle = api.request('POST', '/api/admin/vehicles', {
            'customer_id': customer['id'], 'license_plate': plate, 'brand': 'Fixture',
            'model': 'Entrega', 'year': 2020,
        }, expected=201)['data']
        service = api.request('POST', '/api/admin/services', {
            'name': 'Diagnostico ' + run_id + ' ' + name, 'unit_price': 15000,
        }, expected=201)['data']
        item = api.request('POST', '/api/admin/inventory-items', {
            'name': 'Filtro ' + run_id + ' ' + name, 'type': 'part',
            'unit_price': 2500, 'quantity_available': 10,
        }, expected=201)['data']
        order = api.request('POST', '/api/admin/service-orders', {
            'customer_document': document, 'vehicle_id': vehicle['id'],
            'services': [{'service_id': service['id'], 'quantity': 1}],
            'inventory_items': [{'inventory_item_id': item['id'], 'quantity': 2}],
            'total_amount': 1,
        }, expected=201)['data']
        order_id = order['id']
        require(order['total_amount'] == 20000, 'Orcamento deve ser calculado pelo servidor')
        status = api.request('GET', f'/api/admin/service-orders/{order_id}/status')['data']
        require(status['status'] == 'received', 'Status inicial incorreto')
        minimal = api.request('POST', '/api/client/service-orders/status', {
            'customer_document': document, 'tracking_token': order['tracking_token'],
        }, authenticated=False)['data']
        require(set(minimal) == {'service_order_id', 'status', 'status_label', 'last_transition_at'},
                'Consulta do cliente deve ter payload minimo')
        api.request('POST', '/api/client/service-orders/status', {
            'customer_document': document, 'tracking_token': '0' * 64,
        }, expected=404, authenticated=False)
        for transition in ('start', 'complete'):
            api.request('POST', f'/api/admin/service-orders/{order_id}/diagnosis/{transition}', {})
        payload = {'service_order_id': order_id, 'decision': decision,
                   'occurred_at': datetime.now(timezone.utc).isoformat()}
        body = json.dumps(payload, separators=(',', ':')).encode()
        signature = 'sha256=' + hmac.new(environment['SERVICE_ORDER_WEBHOOK_SECRET'].encode(),
                                          body, hashlib.sha256).hexdigest()
        path = '/api/webhooks/service-orders/budget-decision'
        api.request('POST', path, payload, expected=401, authenticated=False)
        api.request('POST', path, payload, expected=401, signature='sha256=' + '0' * 64,
                    authenticated=False)
        expected_status = 'in_execution' if decision == 'approved' else 'cancelled'
        for _ in range(2):
            response = api.request('POST', path, payload, signature=signature, authenticated=False)
            require(response['data']['status'] == expected_status, 'Decisao externa incorreta')
        remaining = api.request('GET', f"/api/admin/inventory-items/{item['id']}")['data']['quantity_available']
        require(remaining == (8 if decision == 'approved' else 10), 'Estoque incorreto apos repeticao')
        case_mail_after = Api(args.mailpit_url).request('GET', '/api/v1/messages')['total']
        case_sms_after = sms_count(args.compose_dir)
        require(case_mail_after - case_mail_before == (4 if email else 0), f'Quantidade de e-mails incorreta no caso {name}')
        require(case_sms_after - case_sms_before == (4 if phone else 0), f'Quantidade de SMS incorreta no caso {name}')
        orders.append((order_id, expected_status))
        results.append({'case': name, 'order_id': order_id, 'decision': decision,
                        'status': expected_status, 'stock': remaining,
                        'email_events_expected': 4 if email else 0,
                        'sms_events_expected': 4 if phone else 0})
    require(len({row['order_id'] for row in results}) == 4, 'Identificacoes devem ser unicas')
    listing = api.request('GET', '/api/admin/service-orders')['data']
    listed_ids = {row['id'] for row in listing}
    for order_id, status in orders:
        require((order_id in listed_ids) == (status == 'in_execution'), 'Fila inclui terminal ou omite OS ativa')
    mail_after = Api(args.mailpit_url).request('GET', '/api/v1/messages')['total']
    sms_after = sms_count(args.compose_dir)
    require(mail_after - mail_before == 8, 'Mailpit deve receber oito notificacoes dos casos email e ambos')
    require(sms_after - sms_before == 8, 'Log deve registrar oito SMS dos casos telefone e ambos')
    summary = {'api_requests': api.requests, 'cases': results, 'emails_received': mail_after - mail_before,
               'sms_simulated': sms_after - sms_before, 'result': 'passed'}
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(summary, indent=2) + '\n')
    print(json.dumps(summary, indent=2))


def sms_count(directory):
    result = subprocess.run(['docker', 'compose', 'exec', '-T', 'app', 'php', '-r',
                             '$p="storage/logs/laravel.log"; echo is_file($p) ? substr_count(file_get_contents($p), "SMS notification simulated.") : 0;'],
                            cwd=directory, check=True, text=True, capture_output=True)
    return int(result.stdout.strip())


def main():
    parser = argparse.ArgumentParser(description='Validacao HTTP de entrega em ambiente descartavel; cria dados ficticios.')
    parser.add_argument('--url', default='http://127.0.0.1:8081')
    parser.add_argument('--mailpit-url', default='http://127.0.0.1:8025')
    parser.add_argument('--env-file', type=Path, required=True)
    parser.add_argument('--compose-dir', type=Path, required=True)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--allow-fixtures', action='store_true')
    args = parser.parse_args()
    if not args.allow_fixtures:
        parser.error('Use --allow-fixtures somente para ambiente descartavel; os cadastros e OS permanecerao nele.')
    validate(args)


if __name__ == '__main__':
    try:
        main()
    except (OSError, RuntimeError, KeyError, ValueError, subprocess.CalledProcessError) as error:
        print('Validacao falhou: ' + str(error), file=sys.stderr)
        sys.exit(1)
