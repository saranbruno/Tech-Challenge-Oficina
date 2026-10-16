# Roteiro curto para o vídeo final — Fase 2

Autor: Bruno da Silva Saran, Grupo 55. Duração sugerida: 6 a 8 minutos. Limite do enunciado: 15 minutos.

## Antes de gravar

Abra, nesta ordem, o [repositório](https://github.com/saranbruno/Tech-Challenge-Oficina), a [execução completa da CD com o lockfile corrigido](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38829886306), o [Swagger local](http://127.0.0.1:8081/docs), o [Mailpit local](http://127.0.0.1:8025) e o [PDF da entrega](final-delivery.pdf). Não mostre `.env`, Secrets, JWT, token de acompanhamento, assinatura HMAC, logs completos nem configurações privadas.

O Compose de demonstração usa o projeto `oficina-video` e banco próprio. O Kind persistente usa o kubeconfig privado no caminho abaixo. Em um terminal, defina:

```bash
cd /usr/src/Tech-Challenge-Oficina
export KUBECONFIG="$HOME/.kube/tech-challenge-terraform-local.config"
docker compose -p oficina-video ps
kubectl -n oficina get deployment,statefulset,pods,hpa,pvc
```

Confirme `healthy`, pods `Ready`, PVC `Bound` e métricas conhecidas no HPA. Os acessos em `8082` e `8026` dependem dos encaminhamentos abaixo, cada um em seu próprio terminal:

```bash
kubectl -n oficina port-forward --address 127.0.0.1 service/oficina-api 8082:8000
kubectl -n oficina port-forward --address 127.0.0.1 service/mailpit 8026:8025
```

## Telas e falas

### 1. Apresentação — 30 segundos

**Tela:** repositório, depois o desenho da arquitetura no README.

**Fala:** “Sou Bruno da Silva Saran, do Grupo 55. Esta é a Fase 2 do sistema de oficina mecânica. A aplicação Laravel foi ampliada com novas APIs, notificações, Kubernetes, Terraform e integração e entrega contínuas.”

### 2. Arquitetura e deploy — cerca de 1 minuto

**Tela:** os três desenhos no README; depois terminal com os recursos Kubernetes. Para mostrar um rollout real, execute durante a gravação:

```bash
kubectl -n oficina rollout restart deployment/oficina-api
kubectl -n oficina rollout status deployment/oficina-api --timeout=180s
kubectl -n oficina get deployment,statefulset,pods,hpa,pvc
```

**Fala:** “A interface HTTP chama os casos de uso, que aplicam as regras do domínio. PostgreSQL e notificações ficam na infraestrutura. O Terraform provisiona o Kind e o banco persistente. Aqui a API acaba de ser implantada novamente; o PostgreSQL está pronto e o HPA acompanha CPU e memória.”

Se o encaminhamento da API encerrar durante o rollout, reinicie o comando de `port-forward` antes da carga.

### 3. CI/CD — cerca de 1 minuto

**Tela:** execução verde do GitHub Actions; abra os jobs de validação, publicação e deploy. Mostre a imagem com tag SHA, migrations, smoke tests e destruição do cluster temporário.

**Fala:** “O pipeline instala dependências, executa migrations, testes, cobertura e auditorias. Depois publica a imagem no GHCR e cria um Kind temporário, onde implanta banco e aplicação, valida a API e remove os recursos ao final. Esta execução mostra essas etapas concluídas.”

### 4. APIs e notificações — cerca de 1 minuto

**Tela:** Swagger; depois execute a validação com dados fictícios e mostre o resumo e o Mailpit:

```bash
COMPOSE_PROJECT_NAME=oficina-video python3 scripts/validate-delivery.py \
  --env-file .env --compose-dir . \
  --url http://127.0.0.1:8081 --mailpit-url http://127.0.0.1:8025 \
  --output .local/evidence/video/recording-api.json --allow-fixtures
```

**Fala:** “O Swagger documenta as APIs. Nesta validação real, a ordem é aberta com identificação e total calculado pelo servidor. Consultamos o status e enviamos aprovação ou recusa pelo webhook assinado. A aprovação baixa o estoque uma vez; a recusa cancela sem baixa. As notificações seguem os contatos disponíveis: o e-mail aparece no Mailpit e o SMS é simulado localmente.”

### 5. Escalabilidade — cerca de 2 a 3 minutos

**Tela:** em um terminal, execute a carga; no outro, acompanhe HPA e pods. Mantenha a tela até aparecerem réplicas adicionais `Ready` e métricas válidas.

```bash
LOAD_TEST_URL=http://127.0.0.1:8082/up \
LOAD_TEST_DURATION_SECONDS=120 LOAD_TEST_CONCURRENCY=64 \
LOAD_TEST_OUTPUT=.local/evidence/video/recording-load.csv \
bash scripts/load-test.sh
```

```bash
watch -n 5 'kubectl -n oficina get hpa oficina-api; kubectl -n oficina get deployment oficina-api; kubectl -n oficina get pods -l app.kubernetes.io/component=api'
```

**Fala:** “Esta carga envia requisições à API. O Metrics Server mede CPU e memória; o HPA aumenta automaticamente o número de réplicas. Aqui vemos os novos pods prontos, sem intervenção manual. O resumo da carga mostra as respostas recebidas.”

### 6. Encerramento — 20 segundos

**Tela:** PDF final com repositório e desenho da arquitetura.

**Fala:** “O repositório contém código, Docker, manifestos Kubernetes, Terraform, pipelines e documentação das APIs. O PDF reúne a arquitetura e as evidências da entrega. Obrigado.”

## Depois da gravação

Confirme duração de até 15 minutos, áudio legível, deploy, CI/CD, consumo das APIs e escala automática visíveis. Publique no YouTube ou Vimeo como público ou não listado; teste o acesso sem login. Insira o link definitivo no README e em `docs/fase-2/final-delivery.html`, regenere o PDF pelo comando de `docs/fase-2/README.md` e confira os links. Registre a URL e a duração em `docs/fase-2/evidence/day30-final-audit.md`.
