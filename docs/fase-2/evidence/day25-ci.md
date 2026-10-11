# Dia 25 — Integração contínua

Estado: critérios técnicos atendidos em 2026-10-10; registro final preparado em PR para integração em `fase-2`.

## CI aprovada

A [execução de PR 38096062454](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38096062454), commit `df59d7bf22e60498f7638a50589f5902b620307c`, aprovou o job `Validar aplicacao e infraestrutura`:

- Cache Composer restaurado e dependências instaladas; PostgreSQL 18.4 e migrations reais.
- Pint aprovado; 84 testes de domínio, 152 asserções e 100% das 211 linhas monitoradas.
- 192 testes integrados, 693 asserções, um opt-in Mailpit ignorado e 100% das 221 linhas monitoradas. O teste SMTP opt-in passou separadamente no Dia 29.
- OpenAPI, auditoria sem advisories, Terraform fmt/validate nos dois ambientes e kubeconform: nove recursos válidos por overlay, zero inválidos ou erros.
- Build Docker aprovado com `push: false`; relatórios Clover publicados como artefatos.

A [CI por push 38096058706](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38096058706) e a CI reutilizada pela CD também passaram para esse commit. Não houve chamada a SMTP/Twilio pagos. A cobertura informada corresponde às linhas críticas explicitamente monitoradas, não a todo o projeto.

## Falha controlada

A [PR temporária #2](https://github.com/saranbruno/Tech-Challenge-Oficina/pull/2), branch `validacao/dia25-falha-ci`, adicionou um teste PHPUnit intencionalmente falho em um commit isolado `a60742e957957a19606d5884c9e4f9452ed10982`. O teste não entrou em `fase-2` ou `main`.

Na [execução 38096918016](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38096918016), job `114344608197`, cache, instalação, migrations e Pint passaram. A etapa de domínio terminou com 85 testes, 153 asserções e uma falha, mensagem explícita de falha controlada e código de saída 1. Integração, OpenAPI, auditoria, Terraform, Kubernetes e build Docker foram pulados. O upload de cobertura com `always()` passou e o PostgreSQL temporário foi encerrado.

O commit `5520878b05cad1c8ddde75bd27f87ab9c245bd91` removeu o teste por atualização normal da branch, preservando o histórico. Sua árvore é idêntica à baseline `df59d7b`. A [CI de restauração 38097053124](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38097053124) passou, incluindo build Docker. A PR foi encerrada sem merge.

Logs completos preservados em `.local/evidence/day25/valid-pr-ci.log` e `controlled-failure.log`. A falha prova a interrupção do job; não prova, sozinha, uma restrição de merge no GitHub.

## Proteção conferida

Após o usuário salvar as regras, a consulta autenticada dos metadados das duas branches confirmou:

| Branch | protected | Check obrigatório | Aplicação | enforcement_level |
| --- | --- | --- | --- | --- |
| main | true | Validar aplicacao e infraestrutura | GitHub Actions, app_id 15368 | everyone |
| fase-2 | true | Validar aplicacao e infraestrutura | GitHub Actions, app_id 15368 | everyone |

A integração ainda recebe HTTP 403 no endpoint administrativo completo, mas o endpoint de branch expõe o nome do check obrigatório e sua aplicação a todos. A lista de rulesets vazia é compatível com proteção clássica de branch. Não foi necessário alterar permissões ou tentar um merge com teste falhando.

A [CI da PR 38097224986](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38097224986) e a [CD 38097221444](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38097221444) concluíram com sucesso para `6838d6e`.

F2-CI-01 tem evidências técnicas completas: CI verde, falha controlada e check obrigatório para todos nas duas branches. O registro final fica pronto após integrar esta documentação em `fase-2`. Não foram alterados endpoints, migrations, dependências ou regras de negócio. Detalhes em [proteção de branch](../branch-protection.md).
