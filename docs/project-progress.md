# Progresso do projeto

## Identificacao

- Projeto: Tech-Challenge-Oficina
- Autor: Bruno da Silva Saran
- Dia atual: 22
- Estado atual: Concluído

## Roadmap

| Dia | Etapa | Estado | Inicio | Conclusao |
| --- | --- | --- | --- | --- |
| 1 | Base executavel e ambiente local | Concluído | 2026-07-13 | 2026-07-13 |
| 2 | Arquitetura e convencoes do projeto | Concluído | 2026-07-14 | 2026-07-14 |
| 3 | Autenticacao administrativa JWT | Concluído | 2026-07-14 | 2026-07-14 |
| 4 | Dominio e CRUD de clientes | Concluído | 2026-07-15 | 2026-07-15 |
| 5 | Dominio e CRUD de veiculos | Concluído | 2026-07-16 | 2026-07-16 |
| 6 | Catalogo de servicos | Concluído | 2026-07-20 | 2026-07-20 |
| 7 | Pecas, insumos, estoque e movimentacoes | Concluído | 2026-07-21 | 2026-07-21 |
| 8 | Nucleo da Ordem de Servico | Concluído | 2026-07-22 | 2026-07-22 |
| 9 | Composicao inicial da OS | Concluído | 2026-07-22 | 2026-07-22 |
| 10 | Criacao completa da OS e orcamento | Concluído | 2026-07-27 | 2026-07-27 |
| 11 | Recebimento e diagnostico | Concluído | 2026-07-29 | 2026-07-29 |
| 12 | Disponibilizacao do orcamento | Concluído | 2026-07-29 | 2026-07-29 |
| 13 | Acompanhamento seguro pelo cliente | Concluído | 2026-07-29 | 2026-07-29 |
| 14 | Aprovacao, reparos adicionais e cancelamento | Concluído | 2026-07-29 | 2026-07-29 |
| 15 | Integracao transacional com estoque | Concluído | 2026-07-30 | 2026-07-30 |
| 16 | Finalizacao, entrega, listagem e detalhamento | Concluído | 2026-07-30 | 2026-07-30 |
| 17 | Tempo medio e tempo por status | Concluído | 2026-08-02 | 2026-08-02 |
| 18 | Auditoria dos testes de dominio | Concluído | 2026-08-02 | 2026-08-02 |
| 19 | Testes de integracao e fluxo completo | Concluído | 2026-08-02 | 2026-08-02 |
| 20 | OpenAPI e Swagger | Concluído | 2026-08-02 | 2026-08-02 |
| 21 | Linguagem Ubiqua e diagramas DDD | Concluído | 2026-08-02 | 2026-08-02 |
| 22 | Event Storming | Concluído | 2026-08-03 | 2026-08-03 |
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

## Dia 4

### Resumo

Dominio e CRUD administrativo de clientes implementados nas camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP. O cadastro minimo possui nome e CPF/CNPJ. Os documentos sao normalizados, classificados e validados por digitos verificadores antes da persistencia, com unicidade e constraints estruturais tambem aplicadas no PostgreSQL.

### Arquivos principais alterados

- `app/Domain/Customer`
- `app/Application/Customer`
- `app/Infrastructure/Persistence/Eloquent`
- `app/Interfaces/Http/Controllers/Customer/CustomerController.php`
- `app/Interfaces/Http/Requests/Customer`
- `app/Interfaces/Http/Resources/CustomerResource.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `README.md`
- Testes unitarios e de integracao de clientes

### Migrations criadas

- `2026_07_15_000000_create_customers_table.php`

### Endpoints implementados

- `GET /api/admin/customers`
- `POST /api/admin/customers`
- `GET /api/admin/customers/{customer}`
- `PUT /api/admin/customers/{customer}`
- `DELETE /api/admin/customers/{customer}`

### Testes executados

- `docker compose build app`
- `docker compose up -d --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- Rollback e reaplicacao da migration do Dia 4
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `docker compose exec -T app composer audit`
- Validacao de `docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- Migration de clientes aplicada no PostgreSQL.
- Rollback e reaplicacao da migration de clientes concluidos com sucesso.
- PHPUnit: 28 testes aprovados e 80 assercoes, sem falhas, usando PostgreSQL.
- Pint: 61 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu os tres endpoints de autenticacao e os cinco endpoints de clientes.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo alterados; as ocorrencias da busca eram apenas o padrao de rota `api/*`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 4. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 5

### Resumo

Dominio e CRUD administrativo de veiculos implementados nas camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP. Cada veiculo pertence obrigatoriamente a um cliente e possui placa, marca, modelo e ano. Placas brasileiras antigas e Mercosul sao normalizadas e validadas no dominio, com unicidade, formato e integridade referencial tambem protegidos no PostgreSQL.

### Arquivos principais alterados

- `app/Domain/Vehicle`
- `app/Application/Vehicle`
- `app/Infrastructure/Persistence/Eloquent/EloquentVehicleRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/VehicleModel.php`
- `app/Interfaces/Http/Controllers/Vehicle/VehicleController.php`
- `app/Interfaces/Http/Requests/Vehicle`
- `app/Interfaces/Http/Resources/VehicleResource.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `README.md`
- Testes unitarios e de integracao de veiculos

### Migrations criadas

- `2026_07_16_000000_create_vehicles_table.php`

### Endpoints implementados

- `GET /api/admin/vehicles`
- `POST /api/admin/vehicles`
- `GET /api/admin/vehicles/{vehicle}`
- `PUT /api/admin/vehicles/{vehicle}`
- `DELETE /api/admin/vehicles/{vehicle}`

### Testes executados

- `docker compose up -d --build`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- Rollback e reaplicacao da migration do Dia 5
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `docker compose exec -T app composer audit`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e servicos iniciados.
- Migration de veiculos aplicada no PostgreSQL.
- Rollback e reaplicacao da migration de veiculos concluidos com sucesso.
- PHPUnit: 41 testes aprovados e 114 assercoes, sem falhas, usando PostgreSQL.
- Pint: 75 arquivos aprovados, sem problemas de estilo depois da correcao automatica de um arquivo.
- Lista de rotas exibiu os tres endpoints de autenticacao, cinco endpoints de clientes e cinco endpoints de veiculos.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 5. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 6

### Resumo

Catalogo de servicos implementado nas camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP. Cada servico possui somente nome e valor unitario exato em centavos inteiros. Valores negativos sao bloqueados pela validacao HTTP, pelo dominio e por constraint no PostgreSQL.

### Arquivos principais alterados

- `app/Domain/Service`
- `app/Application/Service`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/ServiceModel.php`
- `app/Interfaces/Http/Controllers/Service/ServiceController.php`
- `app/Interfaces/Http/Requests/Service`
- `app/Interfaces/Http/Resources/ServiceResource.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `composer.lock`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `README.md`
- Testes unitarios e de integracao de servicos

### Migrations criadas

- `2026_07_20_000000_create_services_table.php`

### Endpoints implementados

- `GET /api/admin/services`
- `POST /api/admin/services`
- `GET /api/admin/services/{service}`
- `PUT /api/admin/services/{service}`
- `DELETE /api/admin/services/{service}`

### Testes executados

- `docker compose up -d --build`
- `docker compose exec -T app php artisan migrate --force`
- Testes especificos do dominio e CRUD de servicos
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- Rollback e reaplicacao da migration do Dia 6
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `docker compose exec -T app composer audit`
- Atualizacao de seguranca de `guzzlehttp/guzzle` 7.14.1 para 7.15.1 e `guzzlehttp/psr7` 2.12.5 para 2.13.0
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e servicos iniciados.
- Migration de servicos aplicada no PostgreSQL.
- Testes especificos: 12 testes aprovados e 39 assercoes, sem falhas.
- PHPUnit completo: 51 testes aprovados e 150 assercoes, sem falhas, usando PostgreSQL.
- Pint: 88 arquivos aprovados depois da correcao automatica de estilo em `bootstrap/app.php`.
- Lista de rotas exibiu 18 endpoints, incluindo os cinco endpoints de servicos.
- Rollback e reaplicacao da migration de servicos concluidos com sucesso.
- OpenAPI validado sem erros ou avisos.
- O audit inicial encontrou quatro advisories medios em `guzzlehttp/guzzle` 7.14.1; a dependencia foi atualizada para 7.15.1 e o audit final nao encontrou advisories.
- Build Docker final concluido com o lock atualizado e a aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 6. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 7

### Resumo

Catalogo administrativo de pecas e insumos implementado como itens de estoque diferenciados pelos tipos `part` e `supply`. Cada item possui nome, valor unitario exato em centavos e saldo inteiro nao negativo. O estoque inicial positivo e os ajustes manuais geram historico imutavel com administrador responsavel, variacao, saldo anterior e saldo resultante. Os ajustes usam transacao e bloqueio de linha para evitar atualizacoes concorrentes inconsistentes.

### Arquivos principais alterados

- `app/Domain/Inventory`
- `app/Application/Inventory`
- `app/Infrastructure/Persistence/Eloquent/EloquentInventoryItemRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/InventoryItemModel.php`
- `app/Infrastructure/Persistence/Eloquent/Models/StockMovementModel.php`
- `app/Interfaces/Http/Controllers/Inventory/InventoryItemController.php`
- `app/Interfaces/Http/Requests/Inventory`
- `app/Interfaces/Http/Resources/InventoryItemResource.php`
- `app/Interfaces/Http/Resources/StockMovementResource.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `README.md`
- Testes unitarios e de integracao do estoque

### Migrations criadas

- `2026_07_21_000000_create_inventory_items_and_stock_movements_tables.php`

### Endpoints implementados

- `GET /api/admin/inventory-items`
- `POST /api/admin/inventory-items`
- `GET /api/admin/inventory-items/{inventoryItem}`
- `PUT /api/admin/inventory-items/{inventoryItem}`
- `PUT /api/admin/inventory-items/{inventoryItem}/stock`
- `GET /api/admin/inventory-items/{inventoryItem}/movements`
- `DELETE /api/admin/inventory-items/{inventoryItem}`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes especificos do dominio e CRUD de estoque
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- Rollback e reaplicacao da migration do Dia 7
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- Migration das tabelas `inventory_items` e `stock_movements` aplicada no PostgreSQL.
- Testes especificos iniciais: 10 testes aprovados e 50 assercoes, sem falhas.
- PHPUnit completo final: 62 testes aprovados e 201 assercoes, sem falhas, usando PostgreSQL.
- Rollback e reaplicacao da migration do Dia 7 concluidos com sucesso.
- Pint: 106 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu 25 endpoints, incluindo os sete endpoints de estoque.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 7. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 8

### Resumo

Nucleo da Ordem de Servico implementado com os sete estados confirmados, incluindo `cancelled`. A entidade nasce em `received`, permite somente transicoes explicitas do fluxo e restringe o cancelamento aos estados anteriores ao inicio da execucao. Cliente, veiculo, estado atual e instantes do ciclo de vida sao persistidos no PostgreSQL. Nenhum endpoint incompleto de OS foi exposto.

### Arquivos principais alterados

- `app/Domain/ServiceOrder`
- `app/Application/ServiceOrder/Contracts/ServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/ServiceOrderModel.php`
- `app/Providers/AppServiceProvider.php`
- `docs/architecture.md`
- `README.md`
- Testes unitarios e de persistencia da Ordem de Servico

### Migrations criadas

- `2026_07_22_000000_create_service_orders_table.php`

### Endpoints implementados

Nenhum endpoint foi implementado no Dia 8. A criacao incompleta da Ordem de Servico permanece interna ate a conclusao de sua composicao.

### Testes executados

- `docker compose up -d --build`
- `docker compose exec -T app php artisan migrate --force`
- Testes especificos do dominio e persistencia da Ordem de Servico
- Rollback e reaplicacao da migration do Dia 8
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e servicos iniciados.
- Migration de `service_orders` aplicada no PostgreSQL.
- Testes especificos: 15 testes aprovados e 29 assercoes, sem falhas.
- PHPUnit completo: 77 testes aprovados e 230 assercoes, sem falhas, usando PostgreSQL.
- Rollback e reaplicacao da migration do Dia 8 concluidos com sucesso.
- Pint: 115 arquivos aprovados depois da correcao automatica de estilo em `AppServiceProvider.php`.
- Lista de rotas permaneceu com os 25 endpoints anteriores; nenhum endpoint de OS foi publicado.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos criados ou alterados; as ocorrencias da busca eram atributos PHP iniciados por `#[`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 8. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 9

### Resumo

Composicao inicial interna da Ordem de Servico implementada. O caso de uso identifica o cliente por CPF ou CNPJ normalizado, valida que o veiculo pertence ao cliente, exige ao menos um servico e associa quantidades inteiras positivas. Cada item preserva o valor unitario vigente do catalogo como snapshot em centavos. A OS e seus servicos sao persistidos em transacao, sem expor endpoint incompleto.

### Arquivos principais alterados

- `app/Application/ServiceOrder/CreateInitialServiceOrder.php`
- `app/Application/ServiceOrder/Data/RequestedServiceData.php`
- `app/Application/ServiceOrder/Exceptions/VehicleDoesNotBelongToCustomer.php`
- `app/Domain/ServiceOrder/ServiceOrder.php`
- `app/Domain/ServiceOrder/ServiceOrderService.php`
- `app/Application/Customer/Contracts/CustomerRepository.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentCustomerRepository.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/ServiceOrderServiceModel.php`
- `README.md`
- `docs/architecture.md`
- Testes unitarios e de aplicacao da composicao inicial

### Migrations criadas

- `2026_07_22_010000_create_service_order_services_table.php`

### Endpoints implementados

Nenhum endpoint foi implementado no Dia 9. A criacao completa permanece reservada ao Dia 10.

### Testes executados

- `docker compose up -d --build`
- `docker compose exec -T app php artisan migrate --force`
- Testes especificos do dominio, caso de uso e persistencia da Ordem de Servico
- Rollback e reaplicacao da migration do Dia 9
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e servicos iniciados.
- Migration de `service_order_services` aplicada no PostgreSQL.
- Testes especificos finais: 25 testes aprovados e 48 assercoes, sem falhas.
- PHPUnit completo: 87 testes aprovados e 249 assercoes, sem falhas, usando PostgreSQL.
- Rollback e reaplicacao da migration do Dia 9 concluidos com sucesso.
- Pint: 123 arquivos aprovados depois da correcao de tres problemas de estilo.
- Lista de rotas permaneceu com os 25 endpoints anteriores; nenhum endpoint de OS foi publicado.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 9. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 10

### Resumo

Criacao completa da Ordem de Servico implementada por endpoint administrativo protegido por JWT. O caso de uso identifica o cliente por CPF ou CNPJ, valida a propriedade do veiculo, associa servicos, pecas e insumos e captura tipo e valores unitarios como snapshots. Subtotais e total geral sao calculados pelo dominio em centavos inteiros e persistidos com toda a composicao em uma unica transacao. O total enviado pelo consumidor nao e utilizado, e a criacao nao altera o estoque antes do momento definido para o Dia 15.

### Arquivos principais alterados

- `app/Domain/ServiceOrder`
- `app/Application/ServiceOrder/CreateServiceOrder.php`
- `app/Application/ServiceOrder/Data/RequestedInventoryItemData.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/ServiceOrderInventoryItemModel.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `app/Interfaces/Http/Requests/ServiceOrder/StoreServiceOrderRequest.php`
- `app/Interfaces/Http/Resources/ServiceOrderResource.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes unitarios e de integracao da criacao e do orcamento

### Migrations criadas

- `2026_07_27_000000_complete_service_order_composition.php`

### Endpoints implementados

- `POST /api/admin/service-orders`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados do dominio, aplicacao, persistencia e API de Ordem de Servico
- Rollback e reaplicacao da migration do Dia 10
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- Migration de composicao completa aplicada no PostgreSQL.
- Testes focados finais: 35 testes aprovados e 79 assercoes, sem falhas.
- Rollback e reaplicacao da migration do Dia 10 concluidos com sucesso.
- PHPUnit completo: 95 testes aprovados e 277 assercoes, sem falhas, usando PostgreSQL.
- Pint: 133 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu 26 endpoints, incluindo a criacao administrativa da OS.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo criados ou alterados; as ocorrencias da busca eram rotas, atributos PHP e testes preexistentes.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 10. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 11

### Resumo

Inicio controlado do diagnostico implementado como acao administrativa explicita. O caso de uso localiza a Ordem de Servico, solicita ao dominio a transicao exclusiva de `received` para `in_diagnosis` e persiste `diagnosis_started_at`. Repeticoes e chamadas em estados incompativeis sao rejeitadas com HTTP 409 sem substituir o primeiro instante registrado.

### Arquivos principais alterados

- `app/Application/ServiceOrder/StartServiceOrderDiagnosis.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `app/Interfaces/Http/Resources/ServiceOrderResource.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes unitarios e de integracao do inicio do diagnostico

### Migrations criadas

Nenhuma migration criada. A coluna `diagnosis_started_at` ja pertence a estrutura persistente definida no Dia 8.

### Endpoints implementados

- `POST /api/admin/service-orders/{serviceOrder}/diagnosis/start`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados do dominio, endpoint e superficie de rotas
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- PostgreSQL ficou saudavel e todas as migrations estavam aplicadas.
- Testes focados: 19 testes aprovados e 39 assercoes, sem falhas.
- PHPUnit completo: 100 testes aprovados e 291 assercoes, sem falhas, usando PostgreSQL.
- Pint: 135 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu 27 endpoints, incluindo a acao de inicio do diagnostico.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 11. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 12

### Resumo

Conclusao do diagnostico e disponibilizacao do orcamento implementadas como uma acao administrativa explicita. O caso de uso exige a Ordem de Servico em `in_diagnosis`, valida que exista ao menos um servico na composicao persistida, preserva os snapshots e o total calculado pelo servidor, altera automaticamente para `awaiting_approval` e registra `awaiting_approval_at`. O orcamento fica disponivel pela resposta da API, sem notificacao externa.

### Arquivos principais alterados

- `app/Application/ServiceOrder/CompleteServiceOrderDiagnosis.php`
- `app/Domain/ServiceOrder/ServiceOrder.php`
- `app/Domain/ServiceOrder/Exceptions/InvalidServiceOrderBudget.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `app/Interfaces/Http/Resources/ServiceOrderResource.php`
- `bootstrap/app.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes unitarios e de integracao da conclusao do diagnostico

### Migrations criadas

Nenhuma migration criada. A coluna `awaiting_approval_at` e a composicao do orcamento ja pertencem a estrutura persistente implementada anteriormente.

### Endpoints implementados

- `POST /api/admin/service-orders/{serviceOrder}/diagnosis/complete`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados do dominio, conclusao do diagnostico, inicio do diagnostico e superficie de rotas
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- Formatacao direta do teste afetado no checkout compartilhado
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- A primeira execucao focada encontrou duas falhas no fixture novo por tentar inserir timestamps inexistentes em `service_order_services`; o fixture foi corrigido e a execucao focada final aprovou 25 testes e 57 assercoes.
- Build Docker final concluido com sucesso e aplicacao recriada.
- PostgreSQL ficou saudavel e todas as migrations estavam aplicadas.
- PHPUnit completo: 106 testes aprovados e 309 assercoes, sem falhas, usando PostgreSQL.
- O Pint inicial encontrou uma ordem de imports incorreta no teste unitario; o arquivo foi formatado no checkout e a validacao final aprovou 138 arquivos.
- O teste unitario afetado foi repetido apos a formatacao: 14 testes e 24 assercoes aprovados.
- Lista de rotas exibiu 28 endpoints, incluindo a conclusao do diagnostico.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi encontrado nos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 12. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 13

### Resumo

Acompanhamento seguro implementado com CPF ou CNPJ normalizado e token aleatorio de 256 bits especifico da OS. Somente o hash SHA-256 e persistido; o token original aparece uma unica vez na criacao administrativa. A consulta usa POST, exige a combinacao correta e responde 404 sem distinguir documento ou token incorreto. A resposta publica omite cliente, veiculo e token.

### Arquivos principais alterados

- `app/Application/ServiceOrder/TrackServiceOrder.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ClientServiceOrderController.php`
- `app/Interfaces/Http/Requests/ServiceOrder/TrackServiceOrderRequest.php`
- `app/Interfaces/Http/Resources/ClientServiceOrderResource.php`
- `app/Domain/ServiceOrder/ServiceOrder.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `routes/api/client.php`
- `docs/openapi.yaml`

### Migrations criadas

- `2026_07_29_000000_add_tracking_token_hash_to_service_orders.php`

### Endpoints implementados

- `POST /api/client/service-orders/tracking`

### Testes e resultado

- Consulta correta, token incorreto, documento incorreto e ausencia de dados administrativos cobertos.
- Migration aplicada, revertida e reaplicada com sucesso.
- Validacao focada integrada dos Dias 13 e 14: 19 testes e 56 assercoes aprovados.

### Cobertura

Nao medida no Dia 13. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 14

### Resumo

Aprovacao explicita, reparos adicionais e cancelamento implementados. Novos servicos podem ser adicionados apenas em `awaiting_approval`, com snapshots de preco e recalculo do total. A aprovacao altera para `in_execution` e registra o instante sem baixar estoque. O cancelamento permanece permitido somente em `received`, `in_diagnosis` ou `awaiting_approval`.

### Arquivos principais alterados

- `app/Application/ServiceOrder/AddAdditionalRepairs.php`
- `app/Application/ServiceOrder/ApproveClientServiceOrderBudget.php`
- `app/Application/ServiceOrder/CancelServiceOrder.php`
- `app/Domain/ServiceOrder/Exceptions/InvalidAdditionalRepair.php`
- `app/Interfaces/Http/Requests/ServiceOrder/AddAdditionalRepairsRequest.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `README.md`
- `docs/architecture.md`

### Migrations criadas

Nenhuma migration criada no Dia 14.

### Endpoints implementados

- `POST /api/admin/service-orders/{serviceOrder}/additional-repairs`
- `POST /api/client/service-orders/approve`
- `POST /api/admin/service-orders/{serviceOrder}/cancel`

### Testes executados

- Build e recriacao da aplicacao com Docker.
- Testes focados dos fluxos de acompanhamento, reparos, aprovacao, cancelamento e superficie de rotas.
- PHPUnit completo, Pint, Redocly, Composer audit, lista de rotas, healthcheck e `git diff --check`.
- Rollback e reaplicacao da migration do Dia 13.

### Resultado real dos testes

- A primeira consulta publica retornou 422 porque o token original se perdia na reconstituicao; a resposta de criacao foi corrigida para preserva-lo somente em memoria, mantendo apenas o hash no banco.
- Testes focados finais: 19 testes e 56 assercoes aprovados.
- PHPUnit completo final: 109 testes e 338 assercoes aprovados com PostgreSQL.
- Pint final: 149 arquivos aprovados apos formatacao de dois arquivos.
- OpenAPI validado sem erros ou avisos.
- Composer audit sem advisories.
- Lista de rotas: 32 endpoints.
- Aplicacao respondeu HTTP 200 em `/up`.
- Build Docker final concluido e PostgreSQL saudavel.

### Cobertura

Nao medida no Dia 14. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 15

### Resumo

Baixa de pecas e insumos integrada a aprovacao que inicia a execucao da OS. A operacao bloqueia a OS e os itens de estoque em ordem deterministica, valida todos os saldos antes de qualquer alteracao e persiste saldos, movimentacoes e transicao para `in_execution` em uma unica transacao. Estoque insuficiente reverte toda a operacao. A validacao do estado e a unicidade de cada movimento por OS e item impedem baixa duplicada.

### Arquivos principais alterados

- `app/Application/ServiceOrder/ApproveClientServiceOrderBudget.php`
- `app/Application/ServiceOrder/Contracts/ServiceOrderRepository.php`
- `app/Application/ServiceOrder/Exceptions/InsufficientInventoryStock.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Models/StockMovementModel.php`
- `app/Interfaces/Http/Resources/StockMovementResource.php`
- `bootstrap/app.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes de integracao da baixa de estoque

### Migrations criadas

- `2026_07_30_000000_integrate_service_order_stock_consumption.php`

### Endpoints modificados

- `POST /api/client/service-orders/approve`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados de consumo de estoque
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- Rollback e reaplicacao da migration do Dia 15
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- PostgreSQL permaneceu saudavel e a migration do Dia 15 foi aplicada.
- Testes focados: 2 testes aprovados e 23 assercoes, sem falhas.
- PHPUnit completo: 111 testes aprovados e 361 assercoes, sem falhas, usando PostgreSQL.
- Rollback e reaplicacao da migration do Dia 15 concluidos com sucesso.
- Pint: 152 arquivos aprovados, sem problemas de estilo.
- Lista de rotas permaneceu com 32 endpoints; a aprovacao existente passou a realizar a baixa transacional.
- A primeira validacao OpenAPI rejeitou `nullable` do OpenAPI 3.0 em um documento 3.1; os schemas foram corrigidos para tipos combinados e a validacao final passou sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi adicionado aos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 15. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 16

### Resumo

Ciclo administrativo da Ordem de Servico concluido ate `delivered`. A finalizacao aceita exclusivamente uma OS em `in_execution`, altera para `finalized` e registra `finalized_at`. A entrega aceita exclusivamente uma OS em `finalized`, altera para `delivered` e registra `delivered_at`. A API administrativa passou a oferecer listagem paginada e detalhamento completo, sem disponibilizar atualizacao generica de status.

### Arquivos principais alterados

- `app/Application/ServiceOrder/FinalizeServiceOrder.php`
- `app/Application/ServiceOrder/DeliverServiceOrder.php`
- `app/Application/ServiceOrder/ListServiceOrders.php`
- `app/Application/ServiceOrder/GetServiceOrder.php`
- `app/Application/ServiceOrder/Contracts/ServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `app/Interfaces/Http/Resources/ServiceOrderResource.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes de integracao da administracao de Ordens de Servico

### Migrations criadas

Nenhuma migration criada. As colunas `finalized_at` e `delivered_at` ja pertencem a estrutura persistente definida no Dia 8.

### Endpoints implementados

- `GET /api/admin/service-orders`
- `GET /api/admin/service-orders/{serviceOrder}`
- `POST /api/admin/service-orders/{serviceOrder}/finalize`
- `POST /api/admin/service-orders/{serviceOrder}/deliver`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados de dominio, administracao da OS e superficie de rotas
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- PostgreSQL permaneceu saudavel e todas as migrations estavam aplicadas.
- Testes focados: 28 testes aprovados e 72 assercoes, sem falhas.
- PHPUnit completo: 116 testes aprovados e 395 assercoes, sem falhas, usando PostgreSQL.
- Pint: 157 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu 36 endpoints, incluindo as quatro novas operacoes administrativas da OS.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi adicionado aos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 16. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 17

### Resumo

Monitoramento administrativo de duracao implementado para Ordens de Servico entregues. O relatorio calcula em segundos a media do ciclo completo, de `received_at` a `delivered_at`, e a media de cada intervalo entre estados consecutivos. Ordens nao entregues, canceladas ou sem todos os instantes necessarios nao entram no calculo. O resultado geral e retornado sem filtros; periodo de entrega e servico associado podem restringir a amostra.

### Arquivos principais alterados

- `app/Domain/ServiceOrder/ServiceOrderExecutionTimeCalculator.php`
- `app/Application/ServiceOrder/GetServiceOrderExecutionTimeMetrics.php`
- `app/Application/ServiceOrder/Contracts/ServiceOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/EloquentServiceOrderRepository.php`
- `app/Interfaces/Http/Controllers/ServiceOrder/ServiceOrderController.php`
- `app/Interfaces/Http/Requests/ServiceOrder/ServiceOrderExecutionTimeRequest.php`
- `routes/api/admin.php`
- `docs/openapi.yaml`
- `docs/architecture.md`
- `README.md`
- Testes unitarios e de integracao do monitoramento de duracao

### Migrations criadas

Nenhuma migration criada. Os instantes necessarios ja pertencem a estrutura persistente da Ordem de Servico.

### Endpoints implementados

- `GET /api/admin/service-orders-metrics/execution-time`

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- Testes focados do calculo de dominio e da API de metricas
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- PostgreSQL permaneceu saudavel e todas as migrations estavam aplicadas.
- Testes unitarios focados: 2 testes e 6 assercoes aprovados.
- Testes de API focados finais: 4 testes e 20 assercoes aprovados.
- A primeira suite completa encontrou somente a lista fixa de rotas desatualizada; a expectativa foi sincronizada com o endpoint novo.
- PHPUnit completo final: 122 testes e 421 assercoes aprovados, sem falhas, usando PostgreSQL.
- Pint: 162 arquivos aprovados, sem problemas de estilo.
- Lista de rotas exibiu 37 endpoints, incluindo a consulta administrativa de duracao.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi adicionado aos arquivos de codigo criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida no Dia 17. A medicao obrigatoria permanece prevista para os Dias 18 e 19.

## Dia 18

### Resumo

Os testes das regras criticas de dominio foram auditados e a cobertura real foi configurada com PCOV 1.0.12. A medicao ficou delimitada em `phpunit.domain.xml` para as oito classes que implementam CPF/CNPJ, placa, estoque nao negativo, valor monetario exato, composicao e snapshots da OS, transicoes de estado e tempo medio. Foram adicionados testes somente para lacunas reais de servico e item duplicado ou invalido, reparos adicionais e exclusao de ordens incompletas da media.

### Arquivos principais alterados

- `Dockerfile`
- `phpunit.domain.xml`
- `.gitignore`
- `tests/Unit/Domain/ServiceOrder/ServiceOrderBudgetTest.php`
- `tests/Unit/Domain/ServiceOrder/ServiceOrderTest.php`
- `tests/Unit/Domain/ServiceOrder/ServiceOrderExecutionTimeCalculatorTest.php`
- `README.md`
- `docs/architecture.md`
- `docs/project-progress.md`

### Migrations criadas

Nenhuma migration criada.

### Endpoints implementados

Nenhum endpoint criado ou modificado.

### Testes executados

- `docker compose build app`
- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php --ri pcov`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/phpunit -c phpunit.domain.xml --coverage-text --coverage-clover build/coverage-domain.xml`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose config --quiet`
- `docker compose exec -T app composer audit`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo e configuracao criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com PCOV 1.0.12 compilado e habilitado sobre PHP 8.5.8.
- PostgreSQL permaneceu saudavel e todas as migrations foram aplicadas na base de testes.
- Medicao inicial dos testes de dominio: 44 testes e 66 assercoes aprovados; as classes criticas apresentaram entre 80% e 100% de cobertura de linhas.
- PHPUnit completo final: 128 testes e 428 assercoes aprovados, sem falhas, usando PostgreSQL.
- Testes de dominio finais: 50 testes e 73 assercoes aprovados.
- Cobertura final das oito classes criticas: 87,50% das classes, 96,67% dos metodos e 98,79% das linhas.
- `ServiceOrder` atingiu 97,22% das linhas; as outras sete classes criticas atingiram 100% das linhas.
- Pint: 162 arquivos aprovados, sem problemas de estilo.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi adicionado aos arquivos de codigo ou configuracao criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Cobertura real medida com PCOV 1.0.12 e PHPUnit 12.5.31. O resultado final das oito classes criticas foi 87,50% das classes, 96,67% dos metodos e 98,79% das linhas. O relatorio Clover pode ser reproduzido em `build/coverage-domain.xml` e permanece fora do versionamento.

## Dia 19

### Resumo

A suite integrada foi auditada contra todos os fluxos obrigatorios e executada com PostgreSQL. Alem dos testes existentes de autenticacao, CRUDs, criacao, acompanhamento, orçamento, aprovacao, estados, estoque e tempo medio, foi adicionado um teste ponta a ponta que atravessa a API desde os cadastros ate a entrega da OS. A cobertura integrada foi medida com PCOV sobre as oito classes criticas definidas no Dia 18.

### Arquivos principais alterados

- `phpunit.integration.xml`
- `tests/Feature/Api/CompleteServiceOrderFlowTest.php`
- `README.md`
- `docs/architecture.md`
- `docs/project-progress.md`

### Migrations criadas

Nenhuma migration criada.

### Endpoints implementados

Nenhum endpoint criado ou modificado.

### Testes executados

- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app ./vendor/bin/phpunit tests/Feature/Api/CompleteServiceOrderFlowTest.php --display-warnings`
- `docker compose exec -T app ./vendor/bin/phpunit -c phpunit.integration.xml --coverage-text --coverage-clover build/coverage-integration.xml --display-warnings`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose config --quiet`
- `docker compose exec -T app composer audit`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- Requisicao HTTP para `/up`
- Busca por comentarios nos arquivos de codigo e configuracao criados ou alterados
- `git diff --check`

### Resultado real dos testes

- Build Docker concluido com sucesso e aplicacao recriada.
- PostgreSQL permaneceu saudavel e foi usado por todos os Feature Tests.
- Fluxo completo focado: 1 teste e 29 assercoes aprovados.
- Suite integrada com cobertura: 129 testes e 457 assercoes aprovados, sem falhas.
- Cobertura integrada das oito classes criticas: 100% das classes, 100% dos metodos e 100% das linhas, correspondendo a 8 classes, 30 metodos e 165 linhas.
- A suite cobre login JWT, rejeicoes de autenticacao, CRUDs de clientes, veiculos, servicos e estoque, criacao completa da OS, orçamento, snapshots, acompanhamento seguro, aprovação, todos os estados, estoque insuficiente, rollback, repetição, listagem, detalhamento e tempo medio.
- Pint, validacao OpenAPI, Composer audit, healthcheck e verificacoes finais permaneceram sem falhas.
- Nenhum comentario foi adicionado aos arquivos de codigo ou configuracao criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Cobertura integrada medida com PCOV 1.0.12 e PHPUnit 12.5.31 contra PostgreSQL: 100% das oito classes criticas, dos 30 metodos e das 165 linhas. O relatorio reproduzivel e gerado em `build/coverage-integration.xml` e permanece fora do versionamento.

## Dia 20

### Resumo

A especificacao OpenAPI 3.1 foi revisada integralmente contra as rotas implementadas. As 37 operacoes da API possuem correspondencia exata de metodo e caminho, com JWT administrativo, requests, responses, erros, schemas, exemplos e estados da OS documentados. O schema publico da OS foi corrigido para refletir `received_at`, servicos e itens de estoque realmente retornados sem expor IDs administrativos. O Swagger UI 5.32.1 foi disponibilizado em `/docs`, consumindo o contrato servido em `/docs/openapi.yaml`.

### Arquivos principais alterados

- `docs/openapi.yaml`
- `.dockerignore`
- `routes/web.php`
- `resources/views/swagger.blade.php`
- `tests/Feature/SwaggerDocumentationTest.php`
- `README.md`
- `docs/architecture.md`
- `docs/project-progress.md`

### Migrations criadas

Nenhuma migration criada.

### Endpoints adicionados

- `GET /docs`
- `GET /docs/openapi.yaml`

Nenhuma operacao da API de dominio foi criada ou modificada.

### Testes executados

- Comparacao automatica entre `php artisan route:list --path=api --json` e o bundle JSON do OpenAPI
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `npx --yes @redocly/cli bundle docs/openapi.yaml --output=/tmp/oficina-openapi.json --ext=json`
- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app ./vendor/bin/phpunit tests/Feature/SwaggerDocumentationTest.php --display-warnings`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose config --quiet`
- `docker compose exec -T app composer audit`
- Requisicoes HTTP para `/docs`, `/docs/openapi.yaml`, assets do Swagger UI e `/up`
- Comparacao byte a byte entre o OpenAPI versionado e o documento servido
- Busca por comentarios nos arquivos de codigo e configuracao criados ou alterados
- `git diff --check`

### Resultado real dos testes

- A auditoria confirmou 37 rotas de API implementadas e 37 operacoes documentadas, sem ausencias ou operacoes ficticias.
- A primeira validacao OpenAPI encontrou uma chave `example` duplicada; a duplicata foi removida e o lint final passou sem erros ou avisos.
- O primeiro teste do documento servido retornou 500 porque `docs` estava excluido da imagem; o `.dockerignore` foi ajustado para incluir somente `docs/openapi.yaml`.
- O teste seguinte revelou que `BinaryFileResponse` nao expoe o arquivo como corpo textual no ambiente de teste; a verificacao foi corrigida para inspecionar o arquivo associado a resposta.
- Testes documentais finais: 2 testes e 9 assercoes aprovados.
- PHPUnit completo final: 131 testes e 466 assercoes aprovados, sem falhas, usando PostgreSQL.
- Pint: 164 arquivos aprovados, sem problemas de estilo.
- Build Docker concluido com sucesso e aplicacao recriada.
- Swagger UI respondeu HTTP 200 em `/docs`.
- O contrato respondeu HTTP 200 e `application/yaml` em `/docs/openapi.yaml`, identico ao arquivo versionado.
- Os assets JavaScript e CSS fixados do Swagger UI 5.32.1 responderam HTTP 200.
- OpenAPI validado e empacotado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao respondeu HTTP 200 em `/up`.
- Nenhum comentario foi adicionado aos arquivos de codigo ou configuracao criados ou alterados.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida novamente no Dia 20. A medicao integrada do Dia 19 permanece em 100% das oito classes criticas, dos 30 metodos e das 165 linhas.

## Dia 21

### Resumo

A Linguagem Ubiqua foi documentada com o mapeamento entre os termos do dominio em portugues e os identificadores em ingles realmente utilizados no codigo e na API. O glossario registra definicoes, regras, limites e sinonimos que devem ser evitados para Clientes, Veiculos, Servicos, Estoque, Ordem de Servico, Orçamento, Aprovação, ciclo operacional e metricas.

Os diagramas DDD confirmados foram produzidos em Mermaid. O Contexto Estrategico delimita cinco contextos conceituais dentro do monolito; os diagramas de Agregados e Classes de Dominio representam entidades, objetos de valor, enums e limites de consistencia; e os diagramas de Sequencia cobrem criacao e disponibilizacao do orçamento, acompanhamento e aprovacao com consumo transacional do estoque, conclusao do ciclo e monitoramento. Um diagrama de estados consolida as transicoes implementadas da OS.

### Arquivos principais alterados

- `docs/ddd/ubiquitous-language.md`
- `docs/ddd/diagrams.md`
- `docs/architecture.md`
- `README.md`
- `docs/project-progress.md`

### Migrations criadas

Nenhuma migration criada.

### Endpoints adicionados ou modificados

Nenhum endpoint adicionado ou modificado.

### Testes executados

- Renderizacao de todos os blocos Mermaid com `@mermaid-js/mermaid-cli` 11.12.0
- Conferencia dos identificadores documentados contra as interfaces, casos de uso e classes de dominio
- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose config --quiet`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicoes HTTP para `/up` e `/docs`
- `git diff --check`

### Resultado real dos testes

- Os seis blocos Mermaid foram processados sem erro de sintaxe.
- A primeira conferencia encontrou quatro nomes descritivos que nao correspondiam aos metodos concretos do repositorio; os diagramas foram alinhados a `create`, `findForClientOrFail`, `approveForClient` e `completedForMetrics` antes da validacao final.
- Build Docker concluido com sucesso e aplicacao recriada sobre PostgreSQL saudavel.
- Migrations estavam atualizadas, sem novas migrations para aplicar.
- PHPUnit: 131 testes e 466 assercoes aprovados, sem falhas, usando PostgreSQL.
- Pint: 164 arquivos aprovados, sem problemas de estilo.
- Configuracao do Docker Compose valida.
- As 37 rotas da API permaneceram inalteradas; a listagem por `--path=api` tambem inclui a rota documental `/docs/openapi.yaml` por conter o texto `api`.
- OpenAPI validado sem erros ou avisos.
- Composer audit: nenhum advisory de seguranca encontrado.
- Aplicacao e Swagger UI responderam HTTP 200 em `/up` e `/docs`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida novamente no Dia 21, pois a etapa alterou somente documentacao. A medicao integrada do Dia 19 permanece em 100% das oito classes criticas, dos 30 metodos e das 165 linhas.

## Dia 22

### Resumo

O Event Storming foi documentado para os fluxos completos de criacao, acompanhamento e ciclo de vida da Ordem de Servico e para a gestao de Pecas, Insumos e Estoque. A modelagem identifica atores, comandos, eventos de negocio, politicas, agregados, read models, sistemas externos inexistentes, hotspots e caminhos de erro, sempre de acordo com o comportamento implementado.

Os eventos foram registrados como fatos de modelagem, sem sugerir uma infraestrutura inexistente de event bus ou processamento assincrono. Os fluxos detalham a criacao transacional com snapshots e total calculado pelo servidor, diagnostico, disponibilizacao do orçamento pela API, acompanhamento protegido, aprovacao com consumo atomico do estoque, finalizacao, entrega, cancelamento, ajustes administrativos e historico imutavel de movimentacoes.

Durante a validacao, o `composer audit` identificou duas vulnerabilidades publicadas no mesmo dia para o Guzzle 7.15.1, uma alta e uma media. O lock foi atualizado para Guzzle 7.15.2, versao corrigida e compativel, e a imagem, os testes e a auditoria foram executados novamente com sucesso.

### Arquivos principais alterados

- `docs/ddd/event-storming.md`
- `docs/ddd/diagrams.md`
- `README.md`
- `composer.lock`
- `docs/project-progress.md`

### Migrations criadas

Nenhuma migration criada.

### Endpoints adicionados ou modificados

Nenhum endpoint adicionado ou modificado.

### Testes executados

- Renderizacao dos quatro blocos Mermaid do Event Storming com `@mermaid-js/mermaid-cli` 11.12.0
- Validacao de `docs/ddd/event-storming.md` com `markdownlint-cli` 0.47.0 e `MD013` desabilitada
- Conferencia dos eventos, politicas, estados, movimentacoes e operacoes de repositorio contra codigo e migrations
- `docker compose up -d --build --force-recreate app`
- `docker compose exec -T app php artisan migrate --force`
- `docker compose exec -T app ./vendor/bin/phpunit --display-warnings`
- `docker compose exec -T app ./vendor/bin/pint --test`
- `docker compose config --quiet`
- `docker compose exec -T app php artisan route:list --path=api`
- `npx --yes @redocly/cli lint docs/openapi.yaml`
- `docker compose exec -T app composer audit`
- Requisicoes HTTP para `/up` e `/docs`
- `git diff --check`

### Resultado real dos testes

- Os quatro blocos Mermaid foram processados sem erro de sintaxe no container oficial do Mermaid CLI.
- O novo documento passou no markdownlint sem erros; somente a regra de comprimento de linha foi desabilitada para preservar a legibilidade dos diagramas.
- Build Docker concluido e aplicacao recriada com Guzzle 7.15.2 sobre PostgreSQL saudavel.
- Migrations estavam atualizadas, sem novas migrations para aplicar.
- PHPUnit: 131 testes e 466 assercoes aprovados, sem falhas, usando PostgreSQL.
- Pint: 164 arquivos aprovados, sem problemas de estilo.
- Configuracao do Docker Compose valida.
- As 37 operacoes da API permaneceram inalteradas; a listagem apresentou 38 rotas ao incluir tambem `/docs/openapi.yaml`.
- OpenAPI validado sem erros ou avisos.
- Composer audit final: nenhum advisory de seguranca encontrado depois da atualizacao do Guzzle de 7.15.1 para 7.15.2.
- Aplicacao e Swagger UI responderam HTTP 200 em `/up` e `/docs`.
- `git diff --check` nao encontrou erros.

### Cobertura

Nao medida novamente no Dia 22, pois a entrega funcional foi documental e a unica alteracao executavel foi a correcao compativel da dependencia. A medicao integrada do Dia 19 permanece em 100% das oito classes criticas, dos 30 metodos e das 165 linhas.

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
- Manter o cadastro minimo do cliente somente com nome e CPF/CNPJ.
- Manter o catalogo de servicos somente com nome e valor unitario em centavos inteiros.
- Modelar pecas e insumos em um catalogo unico, diferenciados por `part` e `supply`.
- Ajustar o estoque por saldo absoluto em acao explicita, preservando o historico e impedindo exclusao de itens movimentados.

## Duvidas e bloqueios

Nenhum bloqueio atual.

## Pendencias tecnicas

- Selecionar a ferramenta de scan no Dia 23.
- Criar o link da documentacao quando ela existir.
- Confirmar novamente qualquer push ou alteracao externa antes de executar a acao.

## Escopo previsto para o proximo dia

Dia 22: produzir o Event Storming completo da criacao e acompanhamento da OS e da gestao de pecas e insumos, incluindo atores, comandos, eventos, politicas, agregados, read models, hotspots e fluxos de erro coerentes com a implementacao.
