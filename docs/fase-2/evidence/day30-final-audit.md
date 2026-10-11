# Dia 30 — Auditoria final da Fase 2

Início: 2026-10-10. Estado: bloqueado para encerramento, com pendências externas. A autorização do usuário abrange os Dias 29 e 30; a auditoria não representa encerramento da Fase 2.

## Rastreabilidade

| Requisito | Evidência disponível | Situação para a entrega |
| --- | --- | --- |
| F2-ARQ-01 / F2-ARQ-02 | Camadas em `app/Domain`, `app/Application`, `app/Infrastructure`, `app/Interfaces`; testes arquiteturais e diagrama incorporado ao HTML. | Regressão em clone novo aprovada no Dia 29, incluindo os testes arquiteturais. |
| F2-TST-01 | Dia 26: 84 testes de domínio e 192 integrados em PostgreSQL, cobertura de 100% das linhas monitoradas. | Repetição em clone novo aprovada: 84/152 e 192/693, 100% de 211 e 221 linhas monitoradas; opt-in SMTP passou separadamente. |
| F2-OS-01 / F2-OS-02 | Criação transacional, ID explícito, status dedicado administrativo e consulta mínima por documento/token. | Validação HTTP no Dia 29 aprovada, incluindo HMAC inválido, decisões repetidas, estoque e status mínimo. |
| F2-OS-03 / F2-OS-04 | Webhook HMAC, aprovação, recusa para cancelled, idempotência e estoque. | Validação HTTP no Dia 29 aprovada, incluindo HMAC inválido, decisões repetidas, estoque e status mínimo. |
| F2-OS-05 | Prioridade da fila e antiguidade; terminais persistidos fora da fila. | Suite integrada em clone novo aprovada; HTTP confirmou terminais fora da fila. |
| F2-NOT-01 / F2-NOT-02 / F2-NOT-03 | Contatos opcionais; canais independentes; Mailpit/log local e SMTP/Twilio configuráveis. | Quatro casos HTTP aprovados: sem contatos, e-mail, telefone e ambos; oito e-mails reais no Mailpit e oito SMS simulados. |
| F2-CON-01 | Dockerfile único e Compose com PostgreSQL/Mailpit e healthchecks. | Build em clone novo aprovado, três serviços saudáveis, migrations/seed e quatro smokes HTTP 200. README corrigido para geração --show devido ao .env somente leitura. |
| F2-K8S-01 / F2-K8S-02 | Manifestos, migrations, Secrets externos, StatefulSet e PVC; persistência comprovada no Dia 27. | Deploy novo aprovado no Dia 29: API/Mailpit/PostgreSQL prontos, Job Complete e PVC Bound. |
| F2-K8S-03 | Metrics Server e HPA autoscaling/v2 com CPU e memória; Dia 27 demonstrou 1 → 4 → 1. | Clone novo: 4.674 HTTP 200, zero falhas e HPA 1 → 4 Ready com CPU/memória conhecidas; retorno ao mínimo demonstrado no Dia 27. |
| F2-IAC-01 | Terraform local/CI, bootstrap Kind, banco e Metrics Server; destroy temporário comprovado no Dia 26. | Nova reprodução aprovada; estado temporário final vazio e cluster persistente restaurado. |
| F2-IMG-01 | Publicação SHA, labels OCI e pull anônimo comprovados no Dia 26. | Dia 24 concluído: pull anônimo renovado para df59d7b, digest e labels OCI conferidos; CD aprovada. |
| F2-CI-01 | CI completa verde e relatórios de cobertura. | Falha controlada 38096918016 comprovada; proteção de main/fase-2 conferida, check obrigatório para todos; registro final em PR de documentação. |
| F2-CD-01 | Runner hospedado, GHCR, Kind, banco, migrations, smokes, artefatos e destroy always no Dia 26. | Reexecução manual da CI/CD 38094318659, tentativa 2, aprovada nos três jobs; quatro smokes HTTP 200 e 15 recursos destruídos. |
| F2-DOC-01 | README, OpenAPI, arquitetura, DDD, notificações, infraestrutura e CI/CD; 15 diagramas aprovados no Dia 28. | Implementados; artefatos finais em preparação. |
| F2-ENT-01 | HTML com arquitetura incorporada e roteiro alvo 13:30 preparados. | PDF aberto e arquitetura inspecionada; anotações de links conferidas. Gravação, publicação, duração e link do vídeo pendentes. |
| F2-REP-01 | Clone público da branch fase-2 obtido em 2026-10-10. | Acesso do avaliador confirmado; PR #1 em rascunho aberta. Merge com CI verde, tag/release e clone da main pendentes. |

## Verificações externas

- O clone anônimo da branch pública `fase-2` retornou o commit `8b5e69e371c8b565a43a58b05e5cae44b7dfe110`.
- A nova CI por push, execução `38094318653`, concluiu com sucesso. A CD `38094318659` também concluiu. Em seguida, foi acionada manualmente a reexecução do job de CI `114336963700`, que reexecutou os três jobs dependentes na tentativa 2; todos passaram. O evento original permanece `push`, não `workflow_dispatch`. Artefato `11685667165` baixado e conferido; logs confirmam quatro smokes HTTP 200 e 15 recursos destruídos.
- A primeira consulta de permissão de `soat-architecture` retornou HTTP 403. Após o usuário solicitar nova tentativa, a consulta concluiu e confirmou permissão `write`.
- A [PR #1](https://github.com/saranbruno/Tech-Challenge-Oficina/pull/1) foi aberta em rascunho para revisar a Fase 2; não houve merge.
- A leitura das proteções de main e fase-2 ainda retornou HTTP 403 por falta de permissão administrativa da integração; após o usuário configurar as regras, os metadados confirmaram protected=true, check obrigatório Validar aplicacao e infraestrutura do GitHub Actions e enforcement everyone para ambas. Conferência em docs/fase-2/branch-protection.md.
- O enunciado oficial fornecido pelo usuário foi lido integralmente: seis páginas; SHA-256 `f93a4a5cc91ddec19398302f6ad5a027ceaf2a8ee5c510258d9e291d28b34b0e`. A conferência das páginas 3 a 6 está em docs/requirements-phase-2.md e não identificou requisito funcional ausente. O usuário confirmou que o vídeo ainda não foi gravado.

## Pendências de encerramento

1. Dia 29 concluído: reprodução, carga, limpeza temporária e restauração do cluster persistente comprovadas.
2. Enunciado e Dia 24 concluídos; critérios técnicos do Dia 25 atendidos; fechamento registrado na PR #3 para fase-2.
3. Gravar vídeo, publicar como público ou não listado, verificar até 15:00, inserir e testar link no README e PDF.
4. Acesso de `soat-architecture` conferido com permissão `write` em 2026-10-10.
5. Preparar PR revisável e integrar somente depois de todas as condições, CI verde e autorização; validar clone da main.
6. Criar tag/release da Fase 2 após a integração validada.

Não houve merge, tag, release ou alteração de acessos; a PR permanece em rascunho. Nenhum resultado pendente foi declarado concluído.
