# Infraestrutura Kubernetes

O Dia 18 cria a base Kubernetes da API no namespace `oficina`: Namespace, Deployment, Service interno e Job de migrations. A imagem usada pelos recursos e `ghcr.io/saranbruno/tech-challenge-oficina:fase-2` e pode ser substituida pelos overlays dos dias seguintes.

O Deployment expoe a porta 8000, usa os labels `app.kubernetes.io/name` e `app.kubernetes.io/component`, define requests e limits de CPU e memoria e verifica `/up` nas probes de startup, readiness e liveness. O Service `oficina-api` e do tipo `ClusterIP` e seleciona exatamente os pods da API.

O Job `oficina-migrations` executa `php artisan migrate --force`, pode tentar novamente ate tres vezes e expira depois de um dia.

## Configuracao e overlays

O Dia 19 adiciona `configmap.yaml` e um `secretGenerator` com valores ficticios na base. O Deployment e o Job recebem ambos por `envFrom`. Os overlays `local` e `ci` substituem o Secret por valores efemeros distintos e ajustam `APP_ENV`, `APP_DEBUG`, `APP_URL` e o adapter de SMS para `log`.

Cada overlay inclui um Deployment e um Service `ClusterIP` do Mailpit, acessivel internamente pelo nome `mailpit` nas portas 1025 e 8025. O acesso externo e reservado ao Compose local; o overlay CI mantem o Mailpit interno para testes sem servicos pagos. Nunca substitua os valores ficticios por credenciais reais em arquivos versionados.

O arquivo `k8s/secret.env.example` documenta os nomes esperados. Para um ambiente real, gere o Secret fora do Git e aplique-o por um mecanismo seguro. Arquivos `k8s/secret.env` e `k8s/overlays/*/secret.env` sao ignorados pelo Git.

Validacao local:

```bash
kubectl apply -k k8s/base --dry-run=client
```

Validacao dos overlays:

```bash
kubectl apply -k k8s/overlays/local --dry-run=server
kubectl apply -k k8s/overlays/ci --dry-run=server
```

## Terraform e Kind

O Dia 20 adiciona o modulo reutilizavel `infra/modules/kind-cluster`, usado pelos ambientes `infra/environments/local` e `infra/environments/ci`. O modulo executa o binario Kind por `terraform_data`, parametriza endereco e porta da API, configura o provider Kubernetes pelo kubeconfig local e expoe o nome do cluster, o contexto kubectl e o caminho do kubeconfig.

O ambiente local representa o cluster persistente de desenvolvimento. Inicialize e valide com:

```bash
terraform -chdir=infra/environments/local init
terraform -chdir=infra/environments/local validate
terraform -chdir=infra/environments/local plan
terraform -chdir=infra/environments/local apply
kubectl --kubeconfig ~/.kube/tech-challenge-terraform-local.config --context kind-tech-challenge-terraform-local cluster-info
```

O ambiente CI usa o mesmo modulo, com nome separado e ciclo de vida efemero. O runner deve disponibilizar Docker, Kind e Terraform no `PATH`:

```bash
terraform -chdir=infra/environments/ci init
terraform -chdir=infra/environments/ci validate
terraform -chdir=infra/environments/ci apply -auto-approve
kubectl --kubeconfig ~/.kube/tech-challenge-terraform-ci.config --context kind-tech-challenge-terraform-ci cluster-info
terraform -chdir=infra/environments/ci destroy -auto-approve
```

Nao execute `destroy` no ambiente local persistente sem autorizacao. O estado Terraform e os arquivos `.terraform` sao locais e ignorados pelo Git; os arquivos `.terraform.lock.hcl` permanecem versionados para fixar o provider. O modulo nao recebe nem expoe credenciais.

## PostgreSQL no Kubernetes

O Dia 21 adiciona o modulo `infra/modules/postgresql`, aplicado depois do cluster Kind no mesmo ambiente Terraform. Ele cria o namespace `oficina`, um Secret sensível `postgres-secrets`, o Service interno `postgres` e o StatefulSet de uma réplica com PostgreSQL `18.4-alpine`, probes de startup, readiness e liveness, requests/limits e um PVC `data-postgres-0` de `1Gi` na StorageClass `standard`.

O banco não possui porta publicada no host. A aplicação e o Job de migrations usam `DB_HOST=postgres` e recebem usuário e senha pelas chaves `POSTGRES_USER` e `POSTGRES_PASSWORD` do Secret criado pelo Terraform. Senhas reais devem ser fornecidas por variáveis sensíveis ou pelo ambiente de execução; o estado Terraform local não deve ser versionado.

Validação do banco no cluster local:

```bash
terraform -chdir=infra/environments/local plan
kubectl --kubeconfig ~/.kube/tech-challenge-terraform-local.config --context kind-tech-challenge-terraform-local -n oficina get statefulset,pod,pvc,service
kubectl --kubeconfig ~/.kube/tech-challenge-terraform-local.config --context kind-tech-challenge-terraform-local -n oficina exec postgres-0 -- sh -c 'pg_isready -h postgres -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Para testar persistência, grave uma marca temporária com `psql`, recrie somente o pod `postgres-0`, aguarde a condição `Ready`, consulte a marca e remova a tabela de teste. A retenção do PVC é responsabilidade do ambiente local; não use `terraform destroy` para esse teste.

## Metrics Server e HPA

O Dia 22 adiciona o modulo `infra/modules/metrics-server`, compartilhado pelos ambientes Terraform local e CI, e `k8s/base/app-hpa.yaml`, incluido nos dois overlays. O modulo usa o provider Kubernetes ja existente e cria nove recursos: ServiceAccount, dois ClusterRoles, um RoleBinding, dois ClusterRoleBindings, Deployment, Service interno e APIService `v1beta1.metrics.k8s.io`.

A imagem esta fixada em `registry.k8s.io/metrics-server/metrics-server:v0.9.0`. RBAC, probes, porta HTTPS, argumentos e contexto de seguranca seguem o [manifesto oficial da versao 0.9.0](https://github.com/kubernetes-sigs/metrics-server/releases/tag/v0.9.0), compativel com Kubernetes 1.34 ou superior conforme a [matriz oficial](https://github.com/kubernetes-sigs/metrics-server#compatibility-matrix). O Kind deste projeto usa Kubernetes 1.35.0. O Deployment do coletor solicita 100m de CPU e 200Mi de memoria, com limites de 500m e 512Mi.

O modulo desabilita `kubelet_insecure_tls` por padrao. Somente os dois ambientes Kind o habilitam para aceitar os certificados autoassinados dos kubelets. O APIService usa `insecureSkipTLSVerify`, como no manifesto oficial, para o certificado gerado pelo proprio Metrics Server. Essas excecoes sao limitadas aos clusters Kind; outro ambiente exige certificados confiaveis. O coletor permanece interno ao cluster, sem porta publicada no host.

Inicialize novamente o Terraform para descobrir o novo modulo e revise o plano antes de aplicar:

```bash
terraform -chdir=infra/environments/local init
terraform -chdir=infra/environments/local validate
terraform -chdir=infra/environments/local plan
terraform -chdir=infra/environments/local apply
export KUBECONFIG="$HOME/.kube/tech-challenge-terraform-local.config"
kubectl -n kube-system rollout status deployment/metrics-server --timeout=180s
kubectl wait --for=condition=Available apiservice/v1beta1.metrics.k8s.io --timeout=180s
kubectl top nodes
kubectl top pods -n oficina
```

No runner, o mesmo modulo e instalado ao aplicar `infra/environments/ci`, usando o kubeconfig desse ambiente. A instalacao por Terraform nao depende de baixar YAML mutavel durante o apply. O coletor consulta os kubelets a cada 15 segundos; aguarde a inicializacao dos pods e algumas coletas antes de avaliar os targets.

O HPA aponta para o Deployment `oficina-api` e calcula CPU e memoria simultaneamente. Os percentuais usam os requests, conforme o [algoritmo oficial do HPA](https://kubernetes.io/docs/concepts/workloads/autoscaling/horizontal-pod-autoscale/); o controlador escolhe a maior recomendacao de replicas entre as metricas. Os dois alvos nao precisam ser ultrapassados ao mesmo tempo.

| Parametro | Valor |
| --- | --- |
| Replicas minimas / maximas | 1 / 4 |
| Requests da API | CPU 100m; memoria 128Mi |
| Limits da API | CPU 500m; memoria 512Mi |
| Alvo de CPU | 70% do request, equivalente a 70m por pod |
| Alvo de memoria | 80% do request, equivalente a 102,4Mi por pod |
| Subida | Sem estabilizacao adicional; maior permissao entre 2 pods ou 100% a cada 60 segundos |
| Descida | Janela de 300 segundos; no maximo 1 pod removido a cada 60 segundos |

O manifesto do Deployment omite `spec.replicas` para que reaplicar o overlay nao redefina a escala controlada pelo HPA. O limite de quatro replicas restringe a carga no Kind; requests e capacidade disponivel do no ainda podem impedir que novos pods sejam agendados. Memoria que permanece alocada em repouso pode sustentar replicas adicionais. O HPA nao aumenta a capacidade do no nem escala o PostgreSQL.

Com a imagem da API carregada ou acessivel e o Deployment saudavel, aplique e observe:

```bash
kubectl apply -f k8s/base/app-hpa.yaml
kubectl -n oficina rollout status deployment/oficina-api --timeout=180s
kubectl -n oficina wait --for=condition=ScalingActive hpa/oficina-api --timeout=180s
kubectl -n oficina get hpa oficina-api
kubectl -n oficina describe hpa oficina-api
kubectl -n oficina top pods -l app.kubernetes.io/component=api --containers
kubectl -n oficina get deployment oficina-api
kubectl -n oficina get hpa oficina-api --watch
```

`ScalingActive=True` deve ser acompanhado dos dois targets numericos em `get hpa`, sem `<unknown>`, e das replicas dentro do intervalo configurado. Se as metricas nao aparecerem, verifique a disponibilidade do APIService, os logs do Metrics Server, a condicao Ready da API e seus requests. Uma imagem em `ImagePullBackOff` nao fornece metricas para o HPA.

A validacao do Dia 22 usa imagem construida localmente e carregada no Kind, sem publicacao no GHCR. A automacao completa de deploy permanece no Dia 23; testes de carga, resiliência e demonstracao de subida/retorno sob carga permanecem no Dia 27.


### Evidencia local do Dia 22

Em 2026-09-10, Terraform `fmt` e `validate` passaram nos ambientes local e CI, e o plano local terminou sem alteracoes. Os dois overlays passaram no dry-run do servidor. O Metrics Server ficou disponivel e o HPA mostrou `cpu: 6%/70%, memory: 50%/80%`, com uma replica da API `Ready` dentro dos limites de 1 a 4.

A imagem foi construida com `docker build -t tech-challenge-oficina:day22 .` e carregada com `kind load docker-image tech-challenge-oficina:day22 --name tech-challenge-terraform-local`. A renderizacao privada do overlay local recebeu essa imagem no Deployment e no Job, alem de APP_KEY, JWT e HMAC aleatorios. Os manifestos versionados mantem a referencia GHCR para a etapa de publicacao. O Service usa porta **8000**: `/up`, `/docs` e `/docs/openapi.yaml` responderam HTTP 200 em `http://oficina-api:8000`; o Job de migrations concluiu.

O estado Terraform precisou de reimportacao do Deployment do Metrics Server por `Unexpected Identity Change`, com backup privado e sem recriar o recurso. O no Kind foi reiniciado apos um encerramento durante a primeira importacao de imagem; cluster, banco e PVC foram preservados. A segunda importacao concluiu apesar da espera de disco no host. A imagem passou no Pint (255 arquivos) e na suite unitaria (86 testes, 155 assercoes), executados sem rede; cobertura nao foi medida. `kubectl top pods --containers` registrou 5m de CPU e 70Mi de memoria para a API, e `ScalingActive=True` foi confirmado. Os comandos e resultados completos estao no Dia 22 de `docs/project-progress.md`, arquivo local ignorado pelo Git. Nao houve teste de carga nem criacao de cluster CI nesta retomada.
