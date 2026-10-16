# Evidencias do Dia 28: documentacao arquitetural e operacional

Data de conclusao: 2026-10-10.

## Documentacao consolidada

O README descreve o objetivo da Fase 2, as camadas internas, Compose, Kind local persistente, runner temporario, Terraform, Kubernetes, Secrets, GHCR, CI/CD, HPA, webhook, notificacoes, Swagger e testes. Foram incluidos tres desenhos Mermaid: componentes, infraestrutura e fluxo de deploy. A versao do Laravel foi sincronizada com o lockfile, e o contrato OpenAPI atual foi contado em 40 operacoes HTTP.

Os comandos Terraform mostram o bootstrap direcionado ao modulo Kind antes do apply completo e a configuracao privada da senha PostgreSQL. Esse fluxo corresponde ao workflow aprovado no Dia 26 e a automacao local validada anteriormente. A validacao de esquema na CI usa kubeconform; os dry-runs de kubectl exigem acesso a um cluster.

Arquitetura, Linguagem Ubiqua, diagramas DDD, Event Storming e notificacoes foram revisados com os contratos existentes: contatos opcionais, tentativas independentes por canal, webhook HMAC, idempotencia, recusa em `cancelled` e fila operacional. Nenhum contrato HTTP, migration ou arquivo de aplicacao foi alterado neste fechamento.

## Verificacoes executadas

| Verificacao | Resultado real |
| --- | --- |
| `docker compose config --quiet` | Aprovado; renderizacao da configuracao, sem afirmar que o Compose esteja ativo neste momento. |
| Redocly em `docs/openapi.yaml` | Contrato validado. |
| Mermaid CLI 11.12.0 | Quinze blocos Mermaid renderizados em SVG nao vazio, com XML valido: tres no README, oito nos diagramas DDD e quatro no Event Storming. |
| Links relativos | Markdown e ancoras locais conferidos, sem referencias quebradas. O progresso local ignorado pelo Git foi identificado explicitamente no README. |
| Padroes de credenciais | Nenhum token GitHub, chave AWS, JWT completo ou chave privada encontrado nos documentos revisados. A busca nao substitui revisao manual. |
| `composer audit --locked --no-interaction --format=json` | Listas de advisories e pacotes abandonados vazias, com Composer 2.10.2. |
| Semgrep 1.89.0 | 114 regras em 287 arquivos rastreados, zero achados; limitacoes abaixo. |
| `git diff --check` | Aprovado. |

O primeiro processo Mermaid nao iniciou o Chromium dentro do timeout de 30 segundos durante pressao de memoria e disco. Depois de concluirem as outras verificacoes, a renderizacao foi repetida sequencialmente com timeout de inicializacao de 120 segundos e aprovou os 15 diagramas. A falha inicial nao foi tratada como sucesso.

O Semgrep informou analise parcial de oito arquivos por erros de parsing ou internos, alem de duas regras com timeout no PDF historico da Fase 1. A auditoria de dependencias e o scan nao garantem ausencia de vulnerabilidades. Nenhuma dependencia precisou ser atualizada neste fechamento.

As saidas de auditoria, scan e renderizacao ficaram em `.local/evidence/day28`, ignorado pelo Git. O [relatorio de vulnerabilidades](../../vulnerability-report.md) registra a revisao atual e preserva o historico.

## Evidencias de execucao reutilizadas

Os comandos de deploy, migrations e smokes foram comprovados pela [execucao remota 38092377887](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38092377887), documentada no [Dia 26](day26-cd-kind.md). Ela aprovou 84 testes de Dominio e 192 testes de integracao PostgreSQL, com um opt-in ignorado e 100% das linhas monitoradas em ambas as suites. Nenhum codigo de aplicacao ou lockfile foi alterado neste fechamento documental, portanto a suite nao foi repetida apenas por essas edicoes Markdown.

A subida e retorno do HPA e a persistencia apos recriar o PostgreSQL foram comprovadas no Dia 27. Os resultados e comandos estao em [infraestrutura](../../infrastructure.md#carga-hpa-e-resiliencia). SMTP opt-in e Compose com o lockfile atual foram validados em 2026-10-08, conforme o registro operacional local.

## Proximo escopo

O Dia 28 esta concluido. O clone novo, a validacao em ambiente limpo e a preparacao do documento final e roteiro do video pertencem ao Dia 29; video final, PDF final e integracao da branch pertencem ao Dia 30. Esses entregaveis nao foram antecipados para fechar o Dia 28. Os Dias 24 e 25 preservam seu fechamento separado conforme a ordem autorizada.
