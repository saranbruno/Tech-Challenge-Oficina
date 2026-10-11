import argparse
import base64
import fcntl
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import sys
import tempfile


ROOT = Path(__file__).resolve().parents[1]


def run(*args, capture=False, data=None):
    result = subprocess.run(args, input=data, text=True, check=True,
                            stdout=subprocess.PIPE if capture else None)
    return result.stdout if capture else None


def top_level_kind(document):
    lines = document.splitlines()
    for line in lines:
        if line.startswith("kind: "):
            return line.removeprefix("kind: ")
    return None


def other_running_control_planes(cluster):
    names = run("docker", "ps", "--format", "{{.Names}}", capture=True).splitlines()
    expected = cluster + "-control-plane"
    return [name for name in names if name.endswith("-control-plane") and name != expected]


def main():
    parser = argparse.ArgumentParser(description="Deploy local Kind com estado e Secrets privados por ambiente.")
    parser.add_argument("action", choices=["up", "status", "logs", "access", "seed", "destroy"])
    parser.add_argument("--name", default="local")
    parser.add_argument("--temporary", action="store_true")
    parser.add_argument("--confirm-destroy")
    parser.add_argument("--service", choices=["api", "mailpit"], default="api")
    parser.add_argument("--port", type=int)
    args = parser.parse_args()
    if not args.name or len(args.name) > 40 or any(c not in "abcdefghijklmnopqrstuvwxyz0123456789-" for c in args.name):
        parser.error("Nome deve conter apenas letras minusculas, numeros e hifens (maximo 40).")
    os.umask(0o077)
    cluster = "oficina-local-" + args.name
    state = ROOT / ".local" / "k8s" / args.name
    if args.action == "up" and shutil.which("docker"):
        running_control_planes = other_running_control_planes(cluster)
        if running_control_planes:
            parser.error("Pare o outro control plane Kind antes do deploy: " + ", ".join(running_control_planes))
    if args.action != "up" and not (state / "settings.json").exists():
        parser.error("Ambiente nao inicializado por este script.")
    state.mkdir(parents=True, exist_ok=True)
    with (state / "operation.lock").open("w") as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        execute(args, parser, cluster, state)


def execute(args, parser, cluster, state):
    needed = ["kubectl"]
    if args.action in ["up", "destroy"]:
        needed += ["terraform", "kind", "docker"]
    for binary in needed:
        if not shutil.which(binary):
            parser.error(f"Instale {binary} e disponibilize no PATH.")
    if args.action == "up":
        running_control_planes = other_running_control_planes(cluster)
        if running_control_planes:
            parser.error("Pare o outro control plane Kind antes do deploy: " + ", ".join(running_control_planes))
    settings_path = state / "settings.json"
    kubeconfig = state / "kubeconfig"
    if not settings_path.exists():
        clusters = run("kind", "get", "clusters", capture=True).splitlines()
        if cluster in clusters:
            parser.error("Cluster ja existe sem estado deste script; escolha outro nome.")
        settings_path.write_text(json.dumps({"temporary": args.temporary}))
    settings = json.loads(settings_path.read_text())
    if args.temporary and not settings["temporary"]:
        parser.error("Um ambiente persistente nao pode ser convertido em temporario.")
    kubectl = ["kubectl", "--kubeconfig", str(kubeconfig), "--context", "kind-" + cluster]
    ns = kubectl + ["-n", "oficina"]
    infra = state / "infra"
    env = infra / "environments" / "local"
    tf = ["terraform", f"-chdir={env}"]
    if args.action == "destroy":
        if not settings["temporary"] and args.confirm_destroy != cluster:
            parser.error(f"Destruicao permanente exige --confirm-destroy {cluster}; remove o banco e PVC.")
        run(*tf, "destroy", "-input=false", "-auto-approve", "-no-color")
        print("Ambiente destruido. Arquivos privados e repositorio preservados.")
        return
    if args.action == "status":
        run(*ns, "get", "deployment,statefulset,pod,job,service,pvc,hpa")
        return
    if args.action == "logs":
        run(*ns, "logs", "deployment/oficina-api", "--all-containers=true", "--tail=100", "-f")
        return
    if args.action == "access":
        service, remote, default_port = ("oficina-api", 8000, 8082) if args.service == "api" else ("mailpit", 8025, 8026)
        port = args.port or default_port
        print(f"Acesso: http://127.0.0.1:{port}", flush=True)
        run(*ns, "port-forward", "--address=127.0.0.1", "service/" + service, f"{port}:{remote}")
        return
    if args.action == "seed":
        run(*ns, "exec", "deployment/oficina-api", "--", "php", "artisan", "db:seed", "--force")
        print(f"Credenciais do administrador em {state / 'secret.env'}")
        return
    for source in (ROOT / "infra" / "modules").iterdir():
        shutil.copytree(source, infra / "modules" / source.name, dirs_exist_ok=True,
                        ignore=shutil.ignore_patterns(".terraform", "*.tfstate", "*.tfstate.*"))
    env.mkdir(parents=True, exist_ok=True)
    for source in (ROOT / "infra" / "environments" / "local").iterdir():
        if source.suffix == ".tf" or source.name == ".terraform.lock.hcl":
            shutil.copy2(source, env / source.name)
    variables = env / "local.auto.tfvars.json"
    if not variables.exists():
        variables.write_text(json.dumps({
            "cluster_name": cluster, "kubeconfig_path": str(kubeconfig),
            "kind_binary": str(Path(shutil.which("kind")).resolve()),
            "database_password": secrets.token_hex(32), "wait_timeout": "300s",
        }))
    secret_file = state / "secret.env"
    if not secret_file.exists():
        secret_file.write_text("\n".join([
            "APP_KEY=base64:" + base64.b64encode(secrets.token_bytes(32)).decode(),
            "JWT_SECRET=" + secrets.token_hex(32),
            "SERVICE_ORDER_WEBHOOK_SECRET=" + secrets.token_hex(32),
            "ADMIN_PASSWORD=" + secrets.token_hex(24),
            "MAIL_USERNAME=", "MAIL_PASSWORD=", "TWILIO_ACCOUNT_SID=",
            "TWILIO_AUTH_TOKEN=", "TWILIO_FROM=", "",
        ]))
    run("docker", "info", capture=True)
    run(*tf, "init", "-input=false", "-no-color")
    run(*tf, "validate", "-no-color")
    run(*tf, "apply", "-target=module.kind_cluster", "-input=false", "-auto-approve", "-no-color")
    run(*tf, "apply", "-input=false", "-auto-approve", "-no-color")
    run(*ns, "rollout", "status", "statefulset/postgres", "--timeout=300s")
    image = "tech-challenge-oficina:local-" + secrets.token_hex(8)
    run("docker", "build", "-t", image, str(ROOT))
    run("kind", "load", "docker-image", image, "--name", cluster)
    with tempfile.TemporaryDirectory(prefix="render-", dir=state) as temporary:
        render = Path(temporary)
        shutil.copytree(ROOT / "k8s", render / "k8s", ignore=shutil.ignore_patterns("secret.env"))
        overlay = render / "overlay"
        overlay.mkdir()
        shutil.copy2(secret_file, overlay / "secret.env")
        (overlay / "kustomization.yaml").write_text(json.dumps({
            "apiVersion": "kustomize.config.k8s.io/v1beta1", "kind": "Kustomization",
            "resources": ["../k8s/overlays/local"],
            "images": [{"name": "ghcr.io/saranbruno/tech-challenge-oficina", "newName": image.split(":")[0], "newTag": image.split(":")[1]}],
            "secretGenerator": [{"name": "oficina-secrets", "namespace": "oficina", "behavior": "replace", "envs": ["secret.env"]}],
        }))
        manifests = run("kubectl", "kustomize", str(overlay), capture=True)
        documents = manifests.split("\n---\n")
        job = [d for d in documents if top_level_kind(d) == "Job"]
        app = [d for d in documents if top_level_kind(d) == "Deployment" and "name: oficina-api\n" in d]
        config = [d for d in documents if d not in job + app]
        if len(job) != 1 or len(app) != 1:
            raise RuntimeError("Overlay deve conter exatamente um Job e um Deployment da API.")
        run(*kubectl, "apply", "-f", "-", data="\n---\n".join(config))
        run(*ns, "delete", "job", "oficina-migrations", "--ignore-not-found=true", "--wait=true", "--timeout=120s")
        run(*kubectl, "apply", "-f", "-", data=job[0])
        run(*ns, "wait", "--for=condition=complete", "job/oficina-migrations", "--timeout=300s")
        run(*kubectl, "apply", "-f", "-", data=app[0])
    for deployment in ["oficina-api", "mailpit"]:
        run(*ns, "rollout", "status", "deployment/" + deployment, "--timeout=300s")
    smoke = '''foreach (["http://oficina-api:8000/up", "http://oficina-api:8000/docs", "http://oficina-api:8000/docs/openapi.yaml", "http://mailpit:8025/readyz"] as $url) { $ctx = stream_context_create(["http" => ["timeout" => 10, "ignore_errors" => true]]); $body = file_get_contents($url, false, $ctx); $status = $http_response_header[0] ?? ""; if ($body === false || !str_contains($status, " 200 ")) { fwrite(STDERR, "$url $status\\n"); exit(1); } echo "$url HTTP 200\\n"; }'''
    run(*ns, "exec", "deployment/oficina-api", "--", "php", "-r", smoke)
    run(*ns, "get", "deployment,job,pvc,hpa")
    print(f"Deploy concluido: {cluster}. Seed opcional: python3 scripts/k8s-local.py seed --name {args.name}")


if __name__ == "__main__":
    try:
        main()
    except (subprocess.CalledProcessError, OSError, RuntimeError) as error:
        print(f"Falha no deploy local: {error}", file=sys.stderr)
        sys.exit(1)
