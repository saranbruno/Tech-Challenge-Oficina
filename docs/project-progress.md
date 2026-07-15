# Progresso do projeto

## Identificacao

- Projeto: Tech-Challenge-Oficina
- Autor: Bruno da Silva Saran
- Dia atual: 3
- Estado atual: Concluído

## Roadmap

| Dia | Etapa | Estado | Inicio | Conclusao |
| --- | --- | --- | --- | --- |
| 1 | Base executavel e ambiente local | Concluído | 2026-07-13 | 2026-07-13 |
| 2 | Arquitetura e convencoes do projeto | Concluído | 2026-07-14 | 2026-07-14 |
| 3 | Autenticacao administrativa JWT | Concluído | 2026-07-14 | 2026-07-14 |
| 4 | Dominio e CRUD de clientes | Pendente |  |  |
| 5 | Dominio e CRUD de veiculos | Pendente |  |  |
| 6 | Catalogo de servicos | Pendente |  |  |
| 7 | Pecas, insumos, estoque e movimentacoes | Pendente |  |  |
| 8 | Nucleo da Ordem de Servico | Pendente |  |  |
| 9 | Composicao inicial da OS | Pendente |  |  |
| 10 | Criacao completa da OS e orcamento | Pendente |  |  |
| 11 | Recebimento e diagnostico | Pendente |  |  |
| 12 | Disponibilizacao do orcamento | Pendente |  |  |
| 13 | Acompanhamento seguro pelo cliente | Pendente |  |  |
| 14 | Aprovacao, reparos adicionais e cancelamento | Pendente |  |  |
| 15 | Integracao transacional com estoque | Pendente |  |  |
| 16 | Finalizacao, entrega, listagem e detalhamento | Pendente |  |  |
| 17 | Tempo medio e tempo por status | Pendente |  |  |
| 18 | Auditoria dos testes de dominio | Pendente |  |  |
| 19 | Testes de integracao e fluxo completo | Pendente |  |  |
| 20 | OpenAPI e Swagger | Pendente |  |  |
| 21 | Linguagem Ubiqua e diagramas DDD | Pendente |  |  |
| 22 | Event Storming | Pendente |  |  |
| 23 | Scan e analise de vulnerabilidades | Pendente |  |  |
| 24 | Validacao e entrega final | Pendente |  |  |

## Dia 1

### Resumo

Projeto Laravel 13 criado com PHP 8.5, Docker e PostgreSQL 18.4. A imagem foi construida, os servicos iniciaram, o banco ficou saudavel, as migrations padrao foram aplicadas em banco limpo e a aplicacao respondeu por HTTP.

### Arquivos principais alterados

- Base oficial do Laravel
- Dockerfile
- docker-compose.yml
- .dockerignore
- .env.example
- README.md
- docs/project-progress.md

### Migrations criadas

- Migrations padrao de usuarios, cache e jobs fornecidas pelo Laravel

### Endpoints implementados

Nenhum endpoint de API implementado.

### Testes executados

- `docker compose config --quiet`
- `docker compose build`
- `docker compose up -d`
- `docker compose exec -T app php artisan migrate:fresh --force`
- `docker compose exec -T app php artisan migrate:rollback --force`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- Requisicao HTTP para `http://localhost:8081`
- `git diff --check`

### Resultado real dos testes

- Configuracao do Compose valida.
- Build Docker concluido com sucesso.
- PostgreSQL 18.4 iniciou com estado `healthy`.
- Laravel conectou ao PostgreSQL e executou as tres migrations padrao.
- Rollback das tres migrations e nova aplicacao concluidos com sucesso.
- PHPUnit: 2 testes aprovados e 2 assercoes, sem falhas.
- Pint: 26 arquivos aprovados, sem problemas de estilo.
- Aplicacao respondeu HTTP 200 e exibiu `Tech-Challenge-Oficina`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 1.

## Dia 2

### Resumo

Arquitetura em camadas definida para Dominio, Aplicacao, Infraestrutura e Interface HTTP. Os grupos de rotas administrativas e do cliente foram carregados sem publicar endpoints ficticios. O tratamento de erros da API recebeu envelope consistente e a especificacao OpenAPI inicial foi criada somente com os metadados e o schema de erro disponiveis.

### Arquivos principais alterados

- `bootstrap/app.php`
- `app/Interfaces/Http/Responses/ApiErrorResponse.php`
- `routes/api.php`
- `routes/api/admin.php`
- `routes/api/client.php`
- `docs/architecture.md`
- `docs/openapi.yaml`
- `README.md`
- Testes da fundacao HTTP

### Migrations criadas

Nenhuma migration criada.

### Endpoints implementados

Nenhum endpoint de dominio implementado. Os prefixos `/api/admin` e `/api/client` estao configurados para receber as rotas das etapas correspondentes.

### Testes executados

- `docker compose build app`
- `docker compose up -d --force-recreate app`
- `docker compose exec -T app php artisan route:list`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- Requisicao HTTP para `http://localhost:8081/api/unknown`
- Busca por comentarios nos arquivos de codigo alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso.
- PostgreSQL permaneceu saudavel e a aplicacao iniciou com a imagem atualizada.
- Lista de rotas confirmou que nenhum endpoint de dominio foi publicado.
- PHPUnit: 5 testes aprovados e 7 assercoes, sem falhas.
- Pint: 31 arquivos aprovados, sem problemas de estilo.
- Rota inexistente da API respondeu HTTP 404 com o envelope padronizado.
- Nenhum comentario foi encontrado nos arquivos de codigo alterados; as ocorrencias da busca eram apenas o padrao de rota `api/*`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 2.

## Dia 3

### Resumo

Autenticacao administrativa implementada com JWT real por meio de `php-open-source-saver/jwt-auth` 2.9.2. O fluxo inclui login, rota protegida de validacao e renovacao usando o proprio JWT anterior dentro da janela configurada. O administrador inicial e criado por seeder com identidade generica e senha obrigatoria obtida do ambiente.

### Arquivos principais alterados

- `composer.json` e `composer.lock`
- `app/Models/User.php`
- `app/Application/Auth`
- `app/Infrastructure/Auth/JwtAdminTokenProvider.php`
- `app/Interfaces/Http/Controllers/Auth/AdminAuthController.php`
- `app/Interfaces/Http/Requests/Auth/LoginRequest.php`
- `config/auth.php`
- `config/jwt.php`
- `config/initial_admin.php`
- `database/seeders/AdminUserSeeder.php`
- `routes/api/admin.php`
- `.env.example`
- `docs/openapi.yaml`
- `README.md`
- Testes de autenticacao e do seeder

### Migrations criadas

Nenhuma migration criada. A tabela padrao `users` foi adaptada como entidade administrativa minima.

### Endpoints implementados

- `POST /api/admin/auth/login`
- `POST /api/admin/auth/refresh`
- `GET /api/admin/auth/me`

### Testes executados

- `docker compose build app`
- `docker compose up -d --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T -e ADMIN_PASSWORD=<senha-local> app php artisan db:seed --force`
- `docker compose exec -T app php artisan route:list --path=api`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app composer audit`
- `redocly lint docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso.
- PostgreSQL permaneceu saudavel e a aplicacao respondeu HTTP 200 em `/up`.
- Migrations estavam atualizadas e o seeder criou ou atualizou o administrador generico.
- Lista de rotas exibiu somente os tres endpoints administrativos de autenticacao.
- PHPUnit: 14 testes aprovados e 37 assercoes, sem falhas, usando PostgreSQL.
- Pint: 45 arquivos aprovados, sem problemas de estilo.
- Composer audit: nenhum advisory de seguranca encontrado.
- OpenAPI validado sem erros ou avisos.
- Nenhum comentario foi encontrado nos arquivos de codigo alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 3.

## Decisoes confirmadas

- Usar as versoes estaveis mais atuais possiveis.
- Aceitar placas brasileiras antigas e Mercosul.
- Disponibilizar o orcamento pela API.
- Adicionar o estado terminal `Cancelada` a partir de `Recebida`, `Em diagnostico` ou `Aguardando aprovacao`.
- Nao incluir sinal, cobranca ou pagamentos no MVP.
- Reparos adicionais adicionam servicos e recalculam o total da OS.
- Baixar o estoque quando a OS entrar em execucao.
- Manter historico de movimentacoes de estoque.
- Proteger a consulta do cliente com CPF normalizado e token aleatorio especifico da OS.
- Medir o ciclo completo e o tempo em cada status, com filtros e resultado geral quando nenhum filtro for informado.
- Usar a API REST para refletir imediatamente o estado persistido, sem infraestrutura adicional de tempo real.
- Nao ha grupo; Discord do participante: saranbruno.
- Repositorio: https://github.com/saranbruno/Tech-Challenge-Oficina.
- Diagramas previstos: Contexto Estrategico, Agregados, Classes de Dominio e Sequencia dos fluxos principais.
- Biblioteca JWT: `php-open-source-saver/jwt-auth` 2.9.2.
- Implementar refresh token na autenticacao administrativa.
- Criar o administrador inicial por seeder com identidade generica e senha obtida do ambiente.

## Duvidas e bloqueios

Nenhum bloqueio atual.

## Pendencias tecnicas

- Definir os campos minimos concretos do cliente no Dia 4.
- Selecionar a ferramenta de scan no Dia 23.
- Criar o link da documentacao quando ela existir.
- Confirmar novamente qualquer push ou alteracao externa antes de executar a acao.

## Escopo previsto para o proximo dia

Dia 4: confirmar os campos minimos do cliente; implementar dominio, normalizacao e validacao real de CPF e CNPJ; criar migration e constraints; implementar CRUD administrativo protegido por JWT com Form Requests e API Resources; criar testes unitarios e de integracao; atualizar OpenAPI e README.
