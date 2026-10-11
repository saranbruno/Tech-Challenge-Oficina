# Dia 25 — Integração contínua

Estado: bloqueado somente pela configuração de proteção de branch. Validações técnicas concluídas; configuração administrativa adiada pelo usuário para depois.

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

## Única pendência

`main` e `fase-2` estavam com `protected=false` e zero rulesets. A integração retornou HTTP 403 no endpoint administrativo, sem acesso para configurar a proteção. O usuário pediu para terminar o restante e configurar depois. As instruções estão em [proteção de branch](../branch-protection.md).

F2-CI-01 permanece parcial até conferir as regras reais exigindo CI verde. Não foram alterados endpoints, migrations, dependências ou regras de negócio.
