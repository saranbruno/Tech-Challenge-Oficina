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

O Dia 20 adiciona o modulo reutilizavel `infra/modules/kind-cluster`, usado pelos ambientes `infra/environments/local` e `infra/environments/ci`. O modulo executa o binario Kind por `terraform_data`, configura o provider Kubernetes pelo kubeconfig local e expoe o nome do cluster, o contexto kubectl e o caminho do kubeconfig.

O ambiente local representa o cluster persistente de desenvolvimento. Inicialize e valide com:

```bash
terraform -chdir=infra/environments/local init
terraform -chdir=infra/environments/local validate
terraform -chdir=infra/environments/local plan
terraform -chdir=infra/environments/local apply
kubectl --context kind-tech-challenge-terraform-local cluster-info
```

O ambiente CI usa o mesmo modulo, com nome separado e ciclo de vida efemero. O runner deve disponibilizar Docker, Kind e Terraform no `PATH`:

```bash
terraform -chdir=infra/environments/ci init
terraform -chdir=infra/environments/ci validate
terraform -chdir=infra/environments/ci apply -auto-approve
kubectl --context kind-tech-challenge-terraform-ci cluster-info
terraform -chdir=infra/environments/ci destroy -auto-approve
```

Nao execute `destroy` no ambiente local persistente sem autorizacao. O estado Terraform e os arquivos `.terraform` sao locais e ignorados pelo Git; os arquivos `.terraform.lock.hcl` permanecem versionados para fixar o provider. O modulo nao recebe nem expoe credenciais.
