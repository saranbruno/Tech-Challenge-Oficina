# Dia 29 — Reprodução em ambiente limpo

Início: 2026-10-10. Estado: concluído em 2026-10-10. Ambiente limpo reproduzido; artefatos preparados, faltando gravação e link definitivo do vídeo.

## Origem e isolamento

Foi obtido um clone novo e anônimo de `https://github.com/saranbruno/Tech-Challenge-Oficina.git`, branch `fase-2`, commit `8b5e69e371c8b565a43a58b05e5cae44b7dfe110`. Não foram copiados `.env`, dependências, banco, kubeconfig ou estado Terraform do workspace original.

O Compose recebeu projeto `oficina-day29`, portas API 18081, PostgreSQL 15433 e Mailpit 18025/11025, banco próprio e credenciais privadas geradas para esta execução. O cluster persistente foi pausado com autorização do usuário, para evitar dois control planes simultâneos; ele foi restaurado após destruir somente o Kind temporário `oficina-local-day29-20261010`.

Os arquivos brutos estão em `.local/evidence/day29`, ignorados pelo Git. Nenhum segredo aparece nesta evidência.

## Compose e correção das instruções

O build do Dockerfile versionado concluiu no clone novo, reutilizando cache de camadas. Não foi um build sem cache. PostgreSQL, Mailpit e aplicação ficaram saudáveis; 13 migrations existentes e o seed administrativo foram aplicados. API `/up`, Swagger `/docs`, OpenAPI `/docs/openapi.yaml` e Mailpit `/readyz` responderam HTTP 200.

O comando original `php artisan key:generate` falhou com `Read-only file system`, pois o `.env` está montado somente para leitura. O README foi corrigido para `key:generate --show` e `jwt:secret --show`, ambos com `--no-deps`, seguido da configuração privada do `.env`. Os dois comandos corrigidos foram executados e passaram. Senhas de banco/administrador e segredo HMAC também passaram a ser exigidos explicitamente nas instruções.

## Comandos principais executados

No clone novo, após gerar e configurar o `.env` privado:

```bash
docker compose build
docker compose run --rm --no-deps app php artisan key:generate --show
docker compose run --rm --no-deps app php artisan jwt:secret --show
docker compose up -d --force-recreate app
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan db:seed --force
docker compose exec -T postgres psql -U oficina -d postgres -c 'CREATE DATABASE tech_challenge_oficina_test'
docker compose exec -T -e DB_DATABASE=tech_challenge_oficina_test app ./vendor/bin/phpunit -c phpunit.domain.xml --coverage-text --coverage-clover build/coverage-domain.xml
docker compose exec -T -e DB_DATABASE=tech_challenge_oficina_test app ./vendor/bin/phpunit -c phpunit.integration.xml --coverage-text --coverage-clover build/coverage-integration.xml
docker compose exec -T -e DB_DATABASE=tech_challenge_oficina_test -e MAILPIT_INTEGRATION_TEST=true app ./vendor/bin/phpunit -c phpunit.integration.xml --filter MailpitEmailIntegrationTest
docker compose exec -T app ./vendor/bin/pint --test
docker compose exec -T app composer audit --locked --no-interaction --format=json
docker compose exec -T app php artisan route:list --path=api
python3 scripts/k8s-local.py up --name day29-20261010 --temporary
```

Foi usado Terraform 1.16.5 no PATH, com ZIP conferido contra o SHA-256 publicado pela HashiCorp. O Redocly executou via container com o clone montado. O script HTTP em revisão foi executado a partir do workspace, apontando somente ao Compose do clone; suas instruções estão em `docs/fase-2/README.md`. Isso distingue a versão pública inicialmente clonada dos novos artefatos preparados nesta etapa.

## Testes e APIs reais

- PHPUnit domínio: 84 testes, 152 asserções; PCOV com 100% das 211 linhas monitoradas.
- PHPUnit integrado em PostgreSQL: 192 testes, 693 asserções; um opt-in SMTP ignorado; 100% das 221 linhas monitoradas.
- Opt-in SMTP/Mailpit executado separadamente: um teste, uma asserção, aprovado.
- Pint: 255 arquivos aprovados. Composer audit: advisories e pacotes abandonados vazios. Redocly: OpenAPI válido.
- `scripts/validate-delivery.py`: 62 requisições HTTP reais e quatro cenários — sem contatos, somente e-mail, somente telefone e ambos. Foram criados somente dados fictícios no banco isolado.
- HTTP 201 com IDs distintos e total calculado pelo servidor; consulta mínima do cliente; token incorreto retornou 404; assinatura ausente/inválida retornou 401.
- Aprovações resultaram em `in_execution`, estoque 10 → 8, sem nova baixa na repetição. Recusas resultaram em `cancelled`, estoque 10 mantido na repetição. Terminais ficaram fora da fila.
- O cenário sem contatos gerou zero envios; e-mail gerou quatro e-mails e zero SMS; telefone gerou zero e-mails e quatro SMS; ambos gerou quatro de cada. Total: oito mensagens reais no Mailpit e oito SMS simulados no log. Nenhum serviço pago foi chamado.

Os percentuais de cobertura se referem ao escopo crítico configurado nos XML PHPUnit, não a toda a aplicação. A suíte integrada inclui regressões da Fase 1, testes arquiteturais e falhas independentes dos canais. Nenhuma migration ou endpoint foi criado/modificado.

## CI/CD acionada manualmente

A CI por push `38094318653` passou. A [CD 38094318659](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38094318659) passou na execução inicial e foi reexecutada manualmente pela integração GitHub, a partir do job CI `114336963700`.

A tentativa 2 reexecutou todos os jobs: CI `114338280922`, publicação `114338530657` e deploy `114338705837`, todos com sucesso. A reexecução conserva o evento original `push`; não foi um teste de `workflow_dispatch`.

O runner criou Kind/PostgreSQL/Metrics Server, aplicou configuração e migrations, implantou a imagem SHA `8b5e69e371c8b565a43a58b05e5cae44b7dfe110` e passou nos quatro smokes HTTP 200. Os logs confirmaram destruição dos 15 recursos. O artefato `evidencias-kind-38094318659`, ID `11685667165`, foi baixado e conferido: API/Mailpit/PostgreSQL prontos e Job Complete 1/1 com 13 migrations. HPA no snapshot imediato ainda tinha métricas desconhecidas; a nova carga local está registrada abaixo.

## Artefatos acadêmicos

HTML da Fase 2 criado com os três desenhos SVG incorporados, sem dependência dos arquivos ignorados. PDF gerado pelo comando documentado em `docs/fase-2/README.md`: sete páginas A4, arquitetura aberta e inspecionada; URLs representadas como anotações clicáveis. Roteiro alvo 13:30 preparado.

O enunciado oficial fornecido pelo usuário foi lido nas seis páginas e confrontado com a matriz. Não foi identificado novo requisito funcional. O usuário confirmou que o vídeo ainda não foi gravado; gravação, publicação e link definitivo pertencem ao fechamento do Dia 30.

## Kind, carga e limpeza

A automação versionada criou Kind, PostgreSQL/PVC e Metrics Server com Terraform: dois recursos no bootstrap e 13 no plano completo. A imagem foi construída e carregada no nó; Job Complete 1/1 em 27 segundos; API, Mailpit e PostgreSQL prontos; quatro smokes HTTP 200. O PVC temporário ficou Bound.

A carga usou `scripts/load-test.sh`, 64 workers e 120 segundos, com coleta por `scripts/hpa-evidence.sh`. Foram 4.674 respostas HTTP 200 e zero falhas. O HPA começou em uma réplica com CPU/memória conhecidas e chegou a quatro réplicas, todas Ready. A carga via port-forward atingiu um pod; não é evidência de distribuição de tráfego entre os quatro pods. A distribuição pelo Service e retorno ao mínimo foram comprovados separadamente no Dia 27. A coleta atual encerrou após duas amostras de cooldown, sem aguardar todo o retorno ao mínimo.

A primeira destruição foi interrompida com saída 143 depois de remover o container Kind; restou um recurso Terraform no estado. A retentativa idempotente concluiu a remoção desse recurso; o estado final contém zero recursos. Nenhum recurso do ambiente persistente foi destruído. O Compose temporário e seu volume próprio também foram removidos.

O control plane persistente foi religado. Durante a inicialização, o primeiro exec da API encontrou o container ainda ausente; a retentativa respondeu HTTP 200. PostgreSQL completou a recuperação após a pausa e `pg_isready` confirmou conexões disponíveis. API, Mailpit e PostgreSQL voltaram a ficar prontos; o PVC original `ffb8ceff-7a3e-4058-bc34-e19bc5890efb` continua Bound. Somente o control plane persistente permanece em execução.

## Encerramento

F2-TST-01 e F2-NOT-03 foram sincronizados com os testes atuais. F2-ENT-01 permanece parcial porque o vídeo ainda não existe. Enunciado oficial, PDF com arquitetura, roteiro e acesso do avaliador foram conferidos; PR #1 aberta em rascunho. Não houve merge, tag/release, nova migration nem modificação de endpoint.

O Dia 29 está concluído. O Dia 30 depende da gravação/publicação e link do vídeo, dos fechamentos separados dos Dias 24/25 e da integração final. A consulta de proteções de branch continua HTTP 403 na integração; essa limitação não foi tratada como proteção confirmada.
