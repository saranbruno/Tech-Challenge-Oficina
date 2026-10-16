# Dia 30 — Auditoria final da Fase 2

Início: 2026-10-10. Estado: bloqueado para encerramento, com pendências externas. A autorização do usuário abrange os Dias 29 e 30; a auditoria não representa encerramento da Fase 2.

## Vídeo publicado em 2026-10-15

O usuário informou a gravação e publicação de [Tech Challenge - Fase 2 - Grupo 55](https://youtu.be/NPmlpuqKr4Y). A página do YouTube foi consultada sem autenticação; os metadados retornaram `playabilityStatus.status=OK`, `isPrivate=false` e `lengthSeconds=233`, equivalentes a 3 minutos e 53 segundos, dentro do limite de 15 minutos.

O link foi inserido no README principal, no índice dos artefatos e no relatório HTML/PDF da Fase 2. A disponibilidade e a duração foram verificadas; o conteúdo audiovisual não foi assistido nesta conferência. A revisão da presença de deploy, CI/CD, consumo das APIs e escala automática, a integração final autorizada e a reprodução da main continuam necessárias para encerrar a fase.

## Preparação local para a gravação em 2026-10-15

O enunciado oficial no caminho Windows fornecido pelo usuário foi conferido pelo SHA-256 `f93a4a5cc91ddec19398302f6ad5a027ceaf2a8ee5c510258d9e291d28b34b0e`. O roteiro foi reduzido para 6 a 8 minutos, com falas completas, telas e comandos de demonstração. Na preparação inicial, o vídeo ainda não havia sido gravado.

As execuções remotas do commit `759676493b014a58d06f6a78bb1baaad89818df9` falharam em `composer audit`: o lockfile continha `symfony/http-foundation` 8.1.1, afetado por `CVE-2026-108082`. A dependência foi atualizada isoladamente para 8.1.9, e uma nova auditoria local retornou listas vazias de avisos e pacotes abandonados. Build Docker, 84 testes de domínio com 152 asserções e cobertura de 211/211 linhas monitoradas, e 192 testes integrados em PostgreSQL com 693 asserções, um opt-in ignorado e cobertura de 221/221 linhas monitoradas passaram. Essa validação local não substitui uma nova CI/CD remota verde após a publicação da correção.

O projeto Compose isolado `oficina-video` foi criado com volume PostgreSQL próprio, 13 migrations e seed administrativo. API, Swagger e Mailpit retornaram HTTP 200. O verificador HTTP passou após configurar SMTP/Mailpit no `.env` privado: 62 requisições, quatro casos de contato, oito e-mails recebidos e oito SMS simulados. A primeira tentativa falhou porque o `.env` ainda usava mailer `log`; o erro foi corrigido antes da repetição aprovada. Os dados de teste são fictícios e ficaram no volume isolado.

O Kind local existente manteve PostgreSQL/PVC e Metrics Server saudáveis. Uma carga de 120 segundos com 64 workers produziu 4.071 respostas HTTP 200 e zero falhas; o HPA subiu de uma para quatro réplicas `Ready`. A imagem local reconstruída com `symfony/http-foundation` 8.1.9 foi carregada no Kind; o Deployment fez rollout e ficou 4/4 disponível. A API e o Swagger do Kind responderam HTTP 200. Saídas locais estão em `.local/evidence/video`, fora do Git. Os encaminhamentos de porta usam `8082` para API e `8026` para Mailpit do Kind; o Compose isolado usa `8081` e `8025`.

A [CI/CD remota do commit `6a90bb9`](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38829886306) terminou com sucesso nos três jobs: validação, publicação da imagem e implantação no Kind temporário. A API passou nos smoke tests; coleta de evidências e destruição do cluster temporário também passaram. As duas execuções independentes da CI, por push e PR, concluíram com sucesso.

As pendências de gravação, publicação e inserção do link foram resolvidas posteriormente, conforme o registro acima. A revisão do conteúdo do vídeo e a integração final autorizada permanecem pendentes. O resultado desta preparação não fecha o Dia 30.

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
| F2-ENT-01 | HTML/PDF com arquitetura e link do vídeo publicado no YouTube. | Acesso anônimo e duração de 3:53 conferidos; revisão do conteúdo audiovisual pendente. |
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
3. Vídeo publicado, duração de 3:53 e acesso sem login conferidos; link inserido no README e HTML/PDF. Conferir o conteúdo audiovisual exigido pelo enunciado.
4. Acesso de `soat-architecture` conferido com permissão `write` em 2026-10-10.
5. Preparar PR revisável e integrar somente depois de todas as condições, CI verde e autorização; validar clone da main.
6. Criar tag/release da Fase 2 após a integração validada.

Não houve merge, tag, release ou alteração de acessos; a PR permanece em rascunho. Nenhum resultado pendente foi declarado concluído.
