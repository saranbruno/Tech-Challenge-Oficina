# Evidencias do Dia 26: CD em Kind temporario

Data de validacao: 2026-10-10.

Execucao aprovada: [38092377887](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38092377887), na branch `fase-2`, commit `eafd711701aac8b53dd77b1dc2c401d2ac64c120`.

## Resultado no runner hospedado

| Etapa | Evidencia real |
| --- | --- |
| CI reutilizavel | Job `114331274639` concluido com sucesso; dependencias, migrations, Pint, testes, OpenAPI, auditoria, Terraform, Kubernetes e build Docker passaram. |
| Publicacao GHCR | Job `114331600446` concluido com sucesso; imagem publicada com tag SHA. |
| Terraform | Bootstrap criou dois recursos e o apply completo adicionou outros 13; Kind, PostgreSQL e Metrics Server provisionados. |
| Banco | `postgres-0` ficou `1/1 Running`; o Service interno foi criado. |
| Configuracao e migrations | Secrets e ConfigMap aplicados; Job `oficina-migrations` completou `1/1` em 18 segundos. |
| Rollout | API e Mailpit ficaram `1/1` disponiveis, sem reinicios na coleta. |
| Imagem implantada | API e Job de migrations usaram `ghcr.io/saranbruno/tech-challenge-oficina:eafd711701aac8b53dd77b1dc2c401d2ac64c120`. |
| Smoke tests | `/up`, `/docs`, `/docs/openapi.yaml` e Mailpit `/readyz` responderam HTTP 200. |
| Artefatos | `evidencias-kind-38092377887`, ID `11685040492`, incluiu recursos, eventos, logs da API e migrations e outputs Terraform. Cobertura preservada no artefato `cobertura-38092377887`. |
| Destruicao apos sucesso | `Destroy complete! Resources: 15 destroyed.`; o no `tech-challenge-terraform-ci-control-plane` foi removido. |

Os logs dessa execucao mostram a senha do banco e a APP_KEY da CI mascaradas no ambiente exibido pelo runner. Nenhum valor dessas credenciais integra este documento.

## Testes e cobertura

| Suite | Resultado | Linhas monitoradas por PCOV |
| --- | --- | --- |
| Dominio | 84 testes, 152 assercoes | 100%, 211/211 |
| Integracao PostgreSQL | 192 testes, 693 assercoes, um teste opt-in ignorado | 100%, 221/221 |

Os dois overlays Kubernetes validaram nove recursos cada com kubeconform, sem recursos invalidos, erros ou recursos ignorados. `composer audit` nao encontrou avisos de vulnerabilidade. Esses percentuais descrevem as linhas configuradas nos relatorios de cada suite, sem representar cobertura de todo o repositorio.

## Consumo anonimo da imagem

Um `docker pull` foi executado com um diretorio de configuracao Docker novo e vazio, sem credencial GHCR. O download de todas as camadas concluiu para a tag do commit validado.

Digest retornado: `sha256:ff117513bda17c7013191c15bd18fd9f8f48170379b0ad0ee77b808d8961b0d9`.

Os labels OCI `org.opencontainers.image.revision` e `org.opencontainers.image.version` retornaram `eafd711701aac8b53dd77b1dc2c401d2ac64c120`; o label de origem apontou para o repositorio.

## Destruicao apos falha posterior

A execucao anterior [38091893737](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38091893737), commit `89bccc8`, criou o cluster e o PostgreSQL e falhou na renderizacao dos Secrets. Mesmo com essa falha, publicou evidencias e concluiu a destruicao dos 15 recursos Terraform, incluindo o control plane temporario. Isso comprova a execucao da limpeza `always` apos uma falha ocorrida depois do provisionamento.

## Limites da evidencia

O HPA estava presente, mas suas metricas ainda estavam desconhecidas na coleta feita poucos segundos depois do rollout. Essa execucao comprova a CD; a evidencia de escalabilidade e persistencia pertence aos testes do Dia 27 no cluster local. Os arquivos brutos baixados do runner ficaram em `.local/evidence/day26`, ignorado pelo Git. Os artefatos remotos seguem a politica de retencao do GitHub Actions.

O cluster local persistente nao foi usado pelo runner. Os Dias 24 e 25 continuam com fechamento separado conforme a ordem autorizada pelo usuario.
