# Matriz de requisitos da Fase 2

## Finalidade e fontes

Esta matriz converte os requisitos consolidados da Fase 2 em criterios verificaveis e os relaciona com implementacao, testes, evidencia e dias do roadmap. As fontes usadas no Dia 1 foram o enunciado consolidado em `AGENTS.md`, as decisoes aprovadas em `docs/project-progress.md` e a baseline executavel da Fase 1.

O arquivo binario do enunciado oficial da Fase 2 nao esta versionado neste checkout. Por isso, a matriz nao atribui paginas ou citacoes literais ao PDF. Antes da entrega final, os Dias 28 a 30 devem confrontar esta matriz com o PDF disponibilizado para confirmar que nenhuma formulacao adicional ficou de fora.

## Estados da matriz

- `Baseline presente`: o comportamento ja existe na Fase 1, mas ainda sera revisto no dia indicado.
- `Parcial`: parte do requisito existe e a complementacao esta planejada.
- `Pendente`: o requisito sera implementado em dias posteriores.
- `Concluido`: implementacao, teste, documentacao e evidencia foram validados.

## Requisitos funcionais e de qualidade

| ID | Requisito e criterio verificavel | Baseline do Dia 1 | Implementacao prevista | Teste ou evidencia exigida | Dias | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| F2-ARQ-01 | Aplicar Clean Code com nomes claros, responsabilidades coesas, contratos concretos e sem alterar comportamentos validos da Fase 1. | Os Dias 3 e 4 separaram casos de uso e responsabilidades de persistencia, tiparam composicoes, metricas e paginacao e eliminaram `mixed` de Dominio e Aplicacao. | `app/Domain/**`, `app/Application/**`, convencoes em `docs/architecture.md`. | 137 testes e 478 assercoes aprovados em PostgreSQL; cobertura critica e integrada de 100% das 158 linhas monitoradas. | 2, 3 e 4 | Concluido |
| F2-ARQ-02 | Aplicar Clean Architecture com dependencia `Interface HTTP -> Aplicacao -> Dominio`; Eloquent, Laravel, HTTP e fornecedores permanecem fora do Dominio. | Os adapters convertem Eloquent em entidades ou DTOs antes das fronteiras; o modelo administrativo esta na Infraestrutura e os testes arquiteturais protegem imports, helpers, Eloquent e `mixed`. | Contratos internos, adapters de Infraestrutura e testes em `tests/Architecture/**`. | Cinco testes arquiteturais com fixture controlada, mapa de dependencias atualizado e suite funcional aprovada. | 2, 3 e 4 | Concluido |
| F2-TST-01 | Manter testes automatizados dos fluxos criticos em PostgreSQL e cobertura real minima de 80% dos dominios criticos. | 131 testes e 466 assercoes aprovados; 100% das 165 linhas criticas na suite integrada. | Testes unitarios, Feature e de persistencia adicionados junto a cada comportamento. | Relatorios PHPUnit/PCOV, PostgreSQL real e pipeline verde. | 3 a 15, 25 e 29 | Baseline presente |
| F2-OS-01 | Abrir OS somente com cliente, veiculo, servicos e itens previamente cadastrados; validar propriedade, existencia, duplicidade e quantidades; calcular total exato; persistir em transacao; responder 201 com ID unico. | Fluxo por referencias, snapshots, transacao, total em centavos e `id` ja existem; revisao formal pendente. | Request, caso de uso, agregado, repositorio e Resource de OS. | Testes de sucesso, propriedade, duplicidade, rollback, snapshots e resposta 201 com `id`. | 11 e 15 | Baseline presente |
| F2-OS-02 | Oferecer consulta dedicada de status para administrador autenticado e cliente com documento mais token, com payload minimo e sem enumeracao. | Ha detalhamento administrativo e acompanhamento do cliente, mas nao endpoints dedicados de status. | `GET /api/admin/service-orders/{serviceOrder}/status` e `POST /api/client/service-orders/status`. | Autenticacao, combinacoes incorretas indistinguiveis, todos os estados e contrato de resposta. | 10 e 15 | Pendente |
| F2-OS-03 | Receber decisao externa de orcamento em webhook generico com `approved` ou `rejected`, assinatura HMAC do corpo bruto e segredo somente no ambiente. | Existe aprovacao do cliente por documento e token; nao existe webhook nem HMAC. | `POST /api/webhooks/service-orders/budget-decision`, middleware, request, controller e configuracao. | Assinatura ausente, invalida e valida; decisao invalida; segredo ausente de resposta e logs. | 12 e 15 | Pendente |
| F2-OS-04 | Aprovar de forma atomica e sem baixa duplicada; recusar de `awaiting_approval` para `cancelled` sem alterar estoque; repeticoes idempotentes e conflitos coerentes. | A aprovacao atual consome estoque atomicamente; recusa externa e idempotencia do webhook nao existem. | Dominio, caso de uso do webhook e persistencia transacional, preservando o endpoint anterior. | Aprovacao, recusa, repeticao, conflito e contagem das movimentacoes de estoque. | 13 e 15 | Parcial |
| F2-OS-05 | Listar a fila exatamente por `in_execution`, `awaiting_approval`, `in_diagnosis`, `received`, com a OS mais antiga primeiro no mesmo estado; terminais ficam fora sem exclusao fisica. | Ha listagem paginada geral; a consulta priorizada e o historico separado ainda nao existem. | Consulta operacional e consulta historica no repositorio e API administrativa. | Fixture com todos os estados e datas controladas, detalhamento e historico dos terminais. | 14 e 15 | Pendente |
| F2-NOT-01 | Aceitar `email` e `phone` opcionais no cliente, inclusive ambos ausentes, sem `notification_channel`; validar somente valores informados. | Cliente possui apenas nome e documento. | Migration, Dominio, Aplicacao, HTTP, persistencia, factories e OpenAPI. | Criacao e atualizacao sem contatos, com cada contato, com ambos e com formatos invalidos. | 5 | Pendente |
| F2-NOT-02 | Apos cada transicao persistida, tentar todos os canais disponiveis; falhas sao independentes, nao revertem status e operacoes invalidas nao notificam. | Nao ha dispatcher nem notificacoes de transicao. | Portas internas, dispatcher, mensagens por status, adapters e integracao com casos de uso. | Matriz zero/e-mail/SMS/ambos, falha independente e ausencia de envio apos rollback. | 6 e 9 | Pendente |
| F2-NOT-03 | Usar Mailpit e SMS fake/log localmente e em CI; permitir SMTP e Twilio em producao somente por configuracao e Secrets. | E-mail usa driver `log`; nao existem Mailpit, SMS nem Twilio. | Compose, adapters de Infraestrutura, templates, configuracao e documentacao. | E-mail visivel no Mailpit, SMS fake observavel, testes sem rede paga e secrets ausentes do Git. | 7, 8, 9 e 17 | Pendente |

## Requisitos de infraestrutura e entrega

| ID | Requisito e criterio verificavel | Baseline do Dia 1 | Implementacao prevista | Teste ou evidencia exigida | Dias | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| F2-CON-01 | Manter um Dockerfile reproduzivel e um Compose local com aplicacao, PostgreSQL e Mailpit, healthchecks e configuracao segura. | Dockerfile, aplicacao e PostgreSQL existem e sobem; Mailpit e healthcheck da aplicacao ainda nao existem. | `Dockerfile`, `.dockerignore`, `docker-compose.yml`, `.env.example` e README. | Build sem cache, servicos saudaveis, migrations, `/up`, Swagger, Mailpit e SMS fake. | 16 e 17 | Parcial |
| F2-K8S-01 | Versionar Namespace, Deployment, Service, ConfigMap, Secret gerado com seguranca, Job de migrations, probes e recursos em `/k8s`. | Diretorio `/k8s` inexistente. | Bases e overlays `local` e `ci` com Kustomize. | Renderizacao e dry-run, selectors coerentes, Job concluido e pods Ready. | 18 e 19 | Pendente |
| F2-K8S-02 | Executar PostgreSQL no Kubernetes com StatefulSet, Service interno, PVC, probes e credenciais externas ao codigo. | PostgreSQL existe somente no Compose. | Modulo Terraform de PostgreSQL e integracao com o cluster. | Banco Ready, API conectada pelo Service, PVC associado e dados preservados apos recriar o pod. | 21, 23, 26 e 27 | Pendente |
| F2-K8S-03 | Usar HPA `autoscaling/v2` com CPU e memoria simultaneamente, Metrics Server e requests/limits compativeis. | Kubernetes e HPA inexistentes. | Manifesto de HPA, Metrics Server e recursos do Deployment. | `kubectl top`, targets conhecidos de CPU/memoria, scale-up e retorno ao minimo sob carga. | 22 e 27 | Pendente |
| F2-IAC-01 | Provisionar por Terraform o Kind local persistente e o Kind temporario do runner, com `plan`, `apply`, outputs e `destroy` seguro. | Diretorio `/infra` inexistente. | Modulo Kind e ambientes `local` e `ci`. | `terraform fmt`, `validate`, `apply`, `kubectl cluster-info` e `destroy` somente no ambiente temporario. | 20, 23 e 26 | Pendente |
| F2-IMG-01 | Publicar imagem publica no GHCR com tag imutavel por SHA, tag auxiliar `fase-2`, labels OCI e pull anonimo comprovado. | Nao existe workflow nem pacote GHCR comprovado. | Workflow de build, Dockerfile e substituicao controlada da imagem nos overlays. | Publicacao, pull anonimo, healthcheck e identificacao do SHA implantado. | 24 e 26 | Pendente |
| F2-CI-01 | Em PR e push, executar dependencias, build, PostgreSQL, migrations, Pint, testes, cobertura, OpenAPI, audit, Terraform, Kubernetes e build Docker sem publicar em PR. | Validacoes sao locais; workflow de CI inexistente. | `.github/workflows/ci.yml`. | Job verde e falha controlada bloqueando o pipeline, sem secrets em logs. | 25 | Pendente |
| F2-CD-01 | No runner hospedado, publicar imagem, criar Kind temporario, provisionar PostgreSQL, aplicar configuracao e manifests, migrar, aguardar rollouts, fazer smoke, coletar evidencias e destruir sempre. | Workflow de CD inexistente. | `.github/workflows/cd-kind.yml`, Terraform e overlay `ci`. | Logs e artefatos de cada etapa; `terraform destroy` em condicao `always`. | 26 | Pendente |
| F2-DOC-01 | Manter OpenAPI/Swagger, README, arquitetura, DDD, notificacoes, infraestrutura e CI/CD sincronizados apenas com o implementado. | OpenAPI 3.1 valida e documenta 37 operacoes da Fase 1; documentos arquiteturais existem. | Documentos incrementais e revisao completa. | Lint OpenAPI, Swagger executavel, diagramas renderizados, links e comandos testados. | 1, 15 e 28 a 30 | Baseline presente |
| F2-ENT-01 | Entregar video publico ou nao listado com ate 15 minutos demonstrando deploy, CI/CD, APIs e HPA; PDF da Fase 2 com repositorio, arquitetura e link do video. | Existem apenas os artefatos finais da Fase 1 em `docs/`. | Novos artefatos em `docs/fase-2/**`, sem sobrescrever a Fase 1. | Duracao maxima, links clicaveis, demonstracao observavel e PDF inspecionado. | 29 e 30 | Pendente |
| F2-REP-01 | Manter repositorio publico, branch `fase-2`, rastreabilidade por commit e integrar em `main` somente com CI verde e autorizacao. | `main` e `fase-2` remotas apontam para `3cbdb88`; repositorio acessivel por HTTPS. | Historico da branch, PR, verificacao de visibilidade e acesso de `soat-architecture`. | Referencias remotas, pull anonimo quando aplicavel, checks da PR e confirmacao de acesso. | 1, 24 e 30 | Parcial |

## Inventario da baseline do Dia 1

### Codigo e dados

- 43 rotas Laravel no total, das quais 37 sao operacoes da API: 3 de autenticacao, 5 de clientes, 5 de veiculos, 5 de servicos, 7 de estoque, 10 administrativas de OS e 2 do cliente.
- 12 migrations aplicadas em PostgreSQL.
- 17 tabelas publicas: `cache`, `cache_locks`, `customers`, `failed_jobs`, `inventory_items`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens`, `service_order_inventory_items`, `service_order_services`, `service_orders`, `services`, `sessions`, `stock_movements`, `users` e `vehicles`.
- Sete estados de OS: `received`, `in_diagnosis`, `awaiting_approval`, `in_execution`, `finalized`, `delivered` e `cancelled`.
- Integracoes atuais: PostgreSQL, JWT administrativo e Swagger UI; e-mail configurado com driver `log`, fila sincrona e nenhuma integracao paga externa.

### Execucao validada

- Docker 29.5.2, Docker Compose 5.1.4, PHP 8.5.8, Laravel 13.19.0, Composer 2.10.2 e PostgreSQL 18.4.
- Pint aprovado em 164 arquivos.
- Suite critica: 50 testes, 73 assercoes, 87,50% das classes, 96,67% dos metodos e 98,79% das linhas.
- Suite integrada: 131 testes, 466 assercoes e 100% das oito classes, 30 metodos e 165 linhas criticas.
- OpenAPI valido com a configuracao recomendada do Redocly CLI.
- `league/commonmark` atualizado de 2.8.3 para 2.10.0 apos a auditoria inicial detectar seis advisories; auditoria final sem advisories.

## Proibicoes que tambem funcionam como criterios de aceite

- Nao criar frontend, aplicativo mobile, microservicos, cloud permanente, pagamentos, agendamento, fornecedores ou logistica de compras.
- Nao adicionar Redis, RabbitMQ, Kafka ou outro broker para notificacoes sem nova autorizacao.
- Nao criar `notification_channel`, tornar contatos obrigatorios, adicionar WhatsApp, criar `budget_rejected` ou versionar orcamentos.
- Nao criar cadastros implicitamente na abertura da OS, excluir OS fisicamente nem oferecer alteracao generica de status.
- Nao versionar `.env`, kubeconfig privado, estado Terraform com segredo, tokens ou credenciais reais.
