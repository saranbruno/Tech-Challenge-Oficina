# Infraestrutura Kubernetes

O Dia 18 cria a base Kubernetes da API no namespace `oficina`: Namespace, Deployment, Service interno e Job de migrations. A imagem usada pelos recursos e `ghcr.io/saranbruno/tech-challenge-oficina:fase-2` e pode ser substituida pelos overlays dos dias seguintes.

O Deployment expoe a porta 8000, usa os labels `app.kubernetes.io/name` e `app.kubernetes.io/component`, define requests e limits de CPU e memoria e verifica `/up` nas probes de startup, readiness e liveness. O Service `oficina-api` e do tipo `ClusterIP` e seleciona exatamente os pods da API.

O Job `oficina-migrations` executa `php artisan migrate --force`, pode tentar novamente ate tres vezes e expira depois de um dia. ConfigMaps, Secrets, o PostgreSQL dentro do cluster, overlays e HPA serao adicionados nas etapas especificas do roadmap.

Validacao local:

```bash
kubectl apply -k k8s/base --dry-run=client
```
