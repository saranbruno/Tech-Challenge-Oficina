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

## Carga, HPA e resiliencia

O script `scripts/load-test.sh` gera requisicoes concorrentes para um endpoint HTTP e salva cada resposta em CSV. Ele nao cria dados nem exige autenticacao por padrao, usando `/up` como alvo seguro. Para uma API exposta localmente, execute `LOAD_TEST_URL=http://127.0.0.1:8081/up LOAD_TEST_DURATION_SECONDS=120 LOAD_TEST_CONCURRENCY=32 LOAD_TEST_OUTPUT=evidence/load.csv bash scripts/load-test.sh`. A duracao, concorrencia, timeout e arquivo de saida sao configuraveis por variaveis de ambiente.

O script `scripts/hpa-evidence.sh` coleta o estado inicial, amostras de HPA, replicas, pods, metricas, eventos e estado final. Ele pode iniciar o script de carga em paralelo com `LOAD_COMMAND='LOAD_TEST_URL=http://127.0.0.1:8081/up LOAD_TEST_DURATION_SECONDS=300 LOAD_TEST_CONCURRENCY=64 LOAD_TEST_OUTPUT=evidence/load.csv bash scripts/load-test.sh' HPA_OUTPUT_DIR=evidence/hpa HPA_SAMPLE_SECONDS=15 bash scripts/hpa-evidence.sh`. Em um runner, substitua a URL por um endpoint acessivel no cluster ou execute a carga com `kubectl exec`.

Para validar retorno ao minimo, aguarde o cooldown configurado no HPA depois que a carga terminar e confira `kubectl -n oficina get hpa oficina-api`, `kubectl -n oficina get deployment oficina-api` e os arquivos `cooldown-*` gerados. Durante a mesma execucao, recrie somente o pod da API e depois o pod `postgres-0`; a API deve voltar a `Ready` e a marca gravada no PVC deve permanecer. Os arquivos de evidencia devem ser preservados para o video e o PDF. Nenhum resultado de carga ou escala e considerado comprovado antes da execucao desses comandos em um cluster ativo.

## Deploy local automatizado (Dia 23)

Instale Python 3, Docker, Terraform, Kind e kubectl no `PATH`. O Docker deve estar em execucao. Referencias de instalacao: [Kind](https://kind.sigs.k8s.io/docs/user/quick-start/), [Terraform](https://developer.hashicorp.com/terraform/install) e [kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl-linux/). O script usa o modulo existente com Kubernetes 1.35.0 e o lockfile de providers versionado. O primeiro uso requer rede para imagens, dependencias do build e providers.

Na raiz do repositorio:

```bash
python3 scripts/k8s-local.py up
python3 scripts/k8s-local.py status
```

O comando cria `oficina-local-local`, um novo cluster persistente gerenciado exclusivamente pela automacao. O cluster anterior `tech-challenge-terraform-local` e seu estado nao sao adotados nem modificados. Cada `--name` recebe seu proprio cluster, kubeconfig, estado Terraform e Secrets em `.local/k8s/<nome>`, excluido do Git e do contexto Docker. Os arquivos novos usam permissoes privadas; preserve esse diretorio para continuar administrando o ambiente e mantenha backup privado. Nao edite nomes ou caminhos nos arquivos gerados.

O fluxo copia os modulos e a configuracao Terraform para o diretorio privado, gera a senha do PostgreSQL e chaves APP_KEY/JWT/HMAC uma unica vez, cria o cluster antes de inicializar recursos Kubernetes, provisiona PostgreSQL e Metrics Server, constroi uma imagem com tag local unica e a carrega no Kind. A aplicacao usa as credenciais do Secret PostgreSQL criado pelo Terraform. O apply direcionado ao cluster resolve a dependencia inicial do provider; sempre e seguido de apply completo.

O overlay local e composto em diretorio privado temporario. Configuracoes, Secrets, Mailpit e HPA sao aplicados; o Job anterior de migrations e recriado e deve concluir antes de aplicar o Deployment da API. O script aguarda os rollouts e exige HTTP 200 de `/up`, `/docs`, `/docs/openapi.yaml` e do `/readyz` do Mailpit pelos Services internos. Repetir `up` preserva as chaves e o banco e aplica as migrations pendentes. Uma falha interrompe o fluxo e preserva o estado para diagnostico e nova tentativa; nao ha destruicao automatica. Evite executar duas operacoes simultaneas no mesmo ambiente: o script aplica um lock local.

O Kind deste host deve executar um control plane por vez. Antes de criar um ambiente novo, a automacao detecta outros containers `*-control-plane` em execucao e interrompe com o nome encontrado. Pare ou conclua o outro ambiente, entao repita o comando. Esse limite foi confirmado na validacao local; o runner efemero da CI permanece isolado no Dia 26.

### Acesso, logs e administrador opcional

Em terminais separados, mantenha os encaminhamentos ativos:

```bash
python3 scripts/k8s-local.py access
python3 scripts/k8s-local.py access --service mailpit
```

API: `http://127.0.0.1:8082`; Swagger: `http://127.0.0.1:8082/docs`; Mailpit: `http://127.0.0.1:8026`. Use `--port` para trocar a porta do host. O encaminhamento escuta somente em loopback; Ctrl+C encerra o acesso sem parar o cluster.

```bash
python3 scripts/k8s-local.py logs
python3 scripts/k8s-local.py seed
```

O seed e explicito e reutiliza o seeder existente: cria ou atualiza o administrador `dev@email.com`, usando `ADMIN_PASSWORD` de `.local/k8s/local/secret.env`. A senha nao e impressa pela automacao. O seed nao cria dados de demonstracao adicionais. Logs exibem os ultimos 100 registros da API e continuam acompanhando ate Ctrl+C.

### Ambientes temporarios e destruicao

Para uma validacao isolada, use o mesmo nome em todos os comandos:

```bash
python3 scripts/k8s-local.py up --name teste --temporary
python3 scripts/k8s-local.py status --name teste
python3 scripts/k8s-local.py destroy --name teste
```

`--temporary` registra a finalidade na criacao e nao converte um ambiente persistente. `destroy` remove os recursos Terraform, cluster e dados do banco. Arquivos do repositorio e arquivos privados de estado/chaves permanecem. Em ambiente persistente, somente execute com autorizacao explicita e com o nome completo confirmado:

```bash
python3 scripts/k8s-local.py destroy --confirm-destroy oficina-local-local
```

O script nao publica imagens no GHCR. Publicacao pertence ao Dia 24; carga e demonstracao de escala permanecem no Dia 27.

### Evidencia do Dia 23

Em 2026-09-14, duas instalacoes temporarias independentes (`day23-a` e `day23-b`) completaram o fluxo. Em ambas, Terraform criou Kind, PostgreSQL, PVC e Metrics Server; a automacao construiu e carregou a imagem local, o Job de migrations concluiu, API e Mailpit ficaram disponiveis e os quatro smokes internos retornaram HTTP 200. A primeira tambem executou o seed e expôs `/up` e `/docs` por port-forward local, ambos com HTTP 200. Cada destruicao removeu 14 recursos e o respectivo cluster, preservando os arquivos do repositorio. O control plane persistente foi reiniciado e terminou `Ready`, com API, PostgreSQL, Mailpit e HPA saudaveis.
