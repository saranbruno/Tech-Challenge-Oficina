# Tech-Challenge-Oficina

Back-end do Sistema Integrado de Atendimento e Execucao de Servicos para uma oficina mecanica.

Autor: Bruno da Silva Saran.

## Fase 2

A evolucao da Fase 2 ocorre na branch `fase-2` em um roadmap de 30 dias, preservando os comportamentos validos da Fase 1. A [matriz de requisitos](docs/requirements-phase-2.md) relaciona cada requisito consolidado com criterio verificavel, implementacao, teste, evidencia e dias responsaveis. O acompanhamento operacional permanece em `docs/project-progress.md`.

O Dia 1 registrou a baseline reproduzivel, sem adicionar funcionalidade: Compose e PostgreSQL saudaveis, 131 testes integrados aprovados com 466 assercoes, 100% das 165 linhas criticas cobertas e OpenAPI valido. A auditoria inicial encontrou advisories no pacote transitivo `league/commonmark`; o lockfile foi atualizado da versao 2.8.3 para 2.10.0 e a auditoria final nao encontrou advisories.

## Tecnologias

- PHP 8.5.8
- Laravel Framework 13.19.0
- Composer 2.10.2
- PostgreSQL 18.4
- Docker e Docker Compose
- PHPUnit 12

## Requisitos

- Docker 29 ou compativel
- Docker Compose 5 ou compativel

PHP e Composer locais nao sao necessarios para a execucao via Docker.

## Configuracao

Crie o arquivo de ambiente e substitua os valores locais de senha:

```bash
cp .env.example .env
```

As portas padrao deste projeto sao `8081` para a aplicacao e `5433` para o PostgreSQL. Elas podem ser alteradas em `.env` por meio de `APP_PORT` e `DB_FORWARD_PORT`.

Gere a chave da aplicacao depois de construir a imagem:

```bash
docker compose build
docker compose run --rm app php artisan key:generate
```

Gere um segredo JWT e configure a senha do administrador inicial no arquivo `.env`:

```bash
docker compose run --rm app php artisan jwt:secret
```

As variaveis `JWT_TTL` e `JWT_REFRESH_TTL` representam minutos. O valor anterior de `ADMIN_PASSWORD` deve ser substituido por uma senha local segura e nao deve ser versionado.

## Execucao

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

A aplicacao fica disponivel em `http://localhost:8081` com a configuracao padrao.

Consulte o estado dos containers:

```bash
docker compose ps
```

## Testes

Os testes usam PostgreSQL. A base de testes sera configurada junto aos testes de integracao nas etapas correspondentes.

```bash
docker compose exec app php artisan test
```

A imagem inclui PCOV 1.0.12 para medir a cobertura real das classes criticas do dominio. A configuracao `phpunit.domain.xml` delimita explicitamente CPF/CNPJ, placa, estoque, valor monetario, orcamento, snapshots, transicoes da OS e tempo medio.

```bash
docker compose exec app ./vendor/bin/phpunit -c phpunit.domain.xml --coverage-text --coverage-clover build/coverage-domain.xml
```

Na medicao do Dia 18, os 50 testes de dominio executaram 73 assercoes. A cobertura obtida nas oito classes criticas foi de 87,50% das classes, 96,67% dos metodos e 98,79% das linhas. O relatorio Clover e gerado localmente em `build/coverage-domain.xml` e nao e versionado.

A validacao integrada usa PostgreSQL e executa testes unitarios e todos os Feature Tests, incluindo um fluxo completo da OS pela API:

```bash
docker compose exec app ./vendor/bin/phpunit -c phpunit.integration.xml --coverage-text --coverage-clover build/coverage-integration.xml
```

Na medicao final do Dia 24, os 131 testes executaram 466 assercoes. As oito classes criticas atingiram 100% de classes, metodos e linhas durante a suite integrada. O relatorio Clover e gerado localmente em `build/coverage-integration.xml` e nao e versionado.

## Encerramento

```bash
docker compose down
```

Para remover tambem o volume local do banco:

```bash
docker compose down -v
```

## Analise de vulnerabilidades

O Dia 23 executou um scan real do codigo-fonte com Semgrep 1.89.0 e um audit de dependencias com Composer.

- Relatorio tecnico: `docs/vulnerability-report.md`
- Semgrep: 201 arquivos rastreados, 27 regras executadas, 0 findings
- Composer audit: nenhuma advisory encontrada

Os resultados nao substituem revisao manual e testes de comportamento.

## PostgreSQL

PostgreSQL foi escolhido como banco unico da aplicacao e dos testes de integracao por oferecer integridade relacional, transacoes ACID, constraints, controle de concorrencia, representacao monetaria exata e suporte consistente a consultas agregadas.

## Arquitetura

O monolito utiliza DDD pragmatico com as camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP. As regras de dependencia e as convencoes estao descritas em `docs/architecture.md`.

A documentacao do dominio inclui a [Linguagem Ubiqua](docs/ddd/ubiquitous-language.md), os [diagramas DDD](docs/ddd/diagrams.md) de Contexto Estrategico, Agregados, Classes de Dominio e Sequencia dos fluxos principais e o [Event Storming](docs/ddd/event-storming.md) da Ordem de Servico e da gestao de estoque.

As rotas administrativas e do cliente estao separadas sob `/api/admin` e `/api/client`. Os CRUDs implementados usam casos de uso independentes do transporte HTTP e persistencia Eloquent implementada na camada de Infraestrutura.

## OpenAPI e Swagger UI

A especificacao OpenAPI 3.1 esta em `docs/openapi.yaml` e documenta as 37 operacoes realmente implementadas, incluindo autenticacao, parametros, requests, responses, erros, exemplos e os sete estados atuais da OS. O Swagger UI 5.32.1 fica disponivel em `http://localhost:8081/docs` depois que o ambiente Docker inicia. O documento bruto servido para o visualizador pode ser consultado em `http://localhost:8081/docs/openapi.yaml`.

Valide o contrato localmente com:

```bash
npx --yes @redocly/cli lint docs/openapi.yaml
```

## Autenticacao JWT

A API administrativa utiliza `php-open-source-saver/jwt-auth` 2.9.2 com algoritmo HS256 e segredo obtido de `JWT_SECRET`.

O administrador inicial e criado pelo seeder com nome e e-mail configuraveis. Os valores padrao de identidade sao `Administrator` e `dev@email.com`; a senha e obrigatoriamente lida de `ADMIN_PASSWORD`.

```bash
docker compose exec app php artisan db:seed
```

Endpoints disponiveis:

- `POST /api/admin/auth/login`: recebe `email` e `password` e emite o JWT.
- `POST /api/admin/auth/refresh`: recebe o JWT anterior no header Bearer e emite um novo token dentro da janela de renovacao.
- `GET /api/admin/auth/me`: valida o JWT e retorna os dados minimos do administrador.

O refresh usa o proprio JWT anterior. Depois do prazo de acesso ele nao autentica rotas protegidas, mas pode ser enviado ao endpoint de refresh enquanto estiver dentro de `JWT_REFRESH_TTL`.

## Clientes

O cadastro minimo possui `name` e `document`. CPF e CNPJ podem ser enviados com ou sem pontuacao, sao normalizados para somente digitos e validados pelos digitos verificadores antes da persistencia. O PostgreSQL tambem impede documentos duplicados e restringe o tipo e o tamanho estrutural do documento.

Todos os endpoints exigem JWT administrativo:

- `GET /api/admin/customers`
- `POST /api/admin/customers`
- `GET /api/admin/customers/{customer}`
- `PUT /api/admin/customers/{customer}`
- `DELETE /api/admin/customers/{customer}`

## Veiculos

Cada veiculo pertence obrigatoriamente a um cliente e possui placa, marca, modelo e ano. A placa aceita os formatos brasileiros antigo e Mercosul, pode ser enviada com separadores e letras minusculas e e persistida com sete caracteres alfanumericos em maiusculas. A aplicacao e o PostgreSQL impedem placas duplicadas, e a chave estrangeira protege o vinculo com o cliente.

Todos os endpoints exigem JWT administrativo:

- `GET /api/admin/vehicles`
- `POST /api/admin/vehicles`
- `GET /api/admin/vehicles/{vehicle}`
- `PUT /api/admin/vehicles/{vehicle}`
- `DELETE /api/admin/vehicles/{vehicle}`

## Servicos

O catalogo de servicos possui somente `name` e `unit_price`. O valor unitario e informado e persistido como um numero inteiro de centavos, sem `float`, e nao pode ser negativo. A quantidade de um servico sera definida futuramente no item da ordem de servico, sem fazer parte do catalogo.

Todos os endpoints exigem JWT administrativo:

- `GET /api/admin/services`
- `POST /api/admin/services`
- `GET /api/admin/services/{service}`
- `PUT /api/admin/services/{service}`
- `DELETE /api/admin/services/{service}`

## Pecas, insumos e estoque

Pecas e insumos compartilham o catalogo `inventory-items` e sao diferenciados por `type`, com os valores `part` e `supply`. Cada item possui nome, valor unitario inteiro em centavos e quantidade disponivel inteira nao negativa. O PostgreSQL reforca os tipos e limites estruturais.

O saldo inicial positivo gera uma movimentacao `initial_stock`. Ajustes posteriores usam uma acao explicita, executada em transacao com bloqueio da linha, e geram `manual_adjustment` com saldo anterior, variacao, saldo resultante e administrador responsavel. O CRUD comum nao aceita alterar o saldo. Itens com movimentacoes nao podem ser excluidos, preservando o historico.

Na aprovacao do orcamento, a OS e todos os itens solicitados sao bloqueados em uma unica transacao. O sistema valida todos os saldos antes de alterar qualquer item, baixa as quantidades e registra uma movimentacao `service_order_consumption` vinculada a OS. Estoque insuficiente retorna conflito e desfaz toda a operacao. A transicao de estado e a restricao unica das movimentacoes impedem baixa duplicada.

Todos os endpoints exigem JWT administrativo:

- `GET /api/admin/inventory-items`
- `POST /api/admin/inventory-items`
- `GET /api/admin/inventory-items/{inventoryItem}`
- `PUT /api/admin/inventory-items/{inventoryItem}`
- `PUT /api/admin/inventory-items/{inventoryItem}/stock`
- `GET /api/admin/inventory-items/{inventoryItem}/movements`
- `DELETE /api/admin/inventory-items/{inventoryItem}`

## Ordem de Servico

O nucleo da Ordem de Servico relaciona obrigatoriamente cliente e veiculo e inicia em `received`. O fluxo principal permitido e `received`, `in_diagnosis`, `awaiting_approval`, `in_execution`, `finalized` e `delivered`. O estado terminal `cancelled` pode ser alcancado somente antes do inicio da execucao, a partir de `received`, `in_diagnosis` ou `awaiting_approval`.

As mudancas usam operacoes explicitas do dominio e registram os instantes do ciclo de vida. Nao existe alteracao generica de status.

O inicio do diagnostico aceita somente uma OS em `received`, altera automaticamente seu estado para `in_diagnosis` e persiste `diagnosis_started_at`. Repeticoes ou chamadas em outro estado retornam conflito sem substituir o primeiro instante.

A conclusao do diagnostico exige uma OS em `in_diagnosis` com ao menos um servico associado. A acao preserva a composicao e os snapshots calculados na criacao, altera automaticamente para `awaiting_approval` e persiste `awaiting_approval_at`. O orcamento passa a estar disponivel pela resposta da API, sem notificacao externa.

A criacao identifica o cliente por CPF ou CNPJ normalizado, confirma que o veiculo pertence a esse cliente e exige ao menos um servico. Pecas e insumos sao opcionais e podem ser associados com quantidades inteiras positivas. O servidor captura os valores unitarios do catalogo como snapshots, calcula cada subtotal e o total geral em centavos e persiste toda a composicao em transacao. O campo `total_amount` enviado pelo consumidor nao e usado como fonte de verdade.

Alteracoes posteriores nos catalogos nao modificam os valores registrados na OS. A criacao nao altera o saldo dos itens; a baixa ocorre atomicamente quando a aprovacao faz a OS entrar em `in_execution`.

Endpoint protegido por JWT:

- `POST /api/admin/service-orders`
- `GET /api/admin/service-orders`
- `GET /api/admin/service-orders/{serviceOrder}`
- `GET /api/admin/service-orders-metrics/execution-time`
- `POST /api/admin/service-orders/{serviceOrder}/diagnosis/start`
- `POST /api/admin/service-orders/{serviceOrder}/diagnosis/complete`
- `POST /api/admin/service-orders/{serviceOrder}/additional-repairs`
- `POST /api/admin/service-orders/{serviceOrder}/finalize`
- `POST /api/admin/service-orders/{serviceOrder}/deliver`
- `POST /api/client/service-orders/approve`
- `POST /api/admin/service-orders/{serviceOrder}/cancel`

Na criacao, a API administrativa retorna uma unica vez um token aleatorio de 64 caracteres. Somente o hash desse token e persistido. O cliente acompanha a OS enviando o token e seu CPF ou CNPJ para `POST /api/client/service-orders/tracking`; combinacoes incorretas retornam 404 e a resposta nao expoe identificadores do cliente ou veiculo.

Reparos adicionais podem incluir novos servicos somente em `awaiting_approval`, preservando snapshots e recalculando o total. A aprovacao explicita inicia `in_execution` e consome o estoque associado de forma transacional. O cancelamento e permitido somente em `received`, `in_diagnosis` ou `awaiting_approval`.

A finalizacao aceita somente uma OS em `in_execution`, altera para `finalized` e registra `finalized_at`. A entrega aceita somente uma OS em `finalized`, altera para `delivered` e registra `delivered_at`. Listagem e detalhamento administrativos apresentam a composicao e os instantes persistidos, sem disponibilizar edicao generica de status.

O monitoramento administrativo considera somente OS em `delivered` com todos os instantes do fluxo principal. A duracao completa vai de `received_at` a `delivered_at`; o tempo de cada status e o intervalo ate a transicao seguinte. As medias sao retornadas em segundos, arredondadas para o inteiro mais proximo, junto da quantidade de ordens elegiveis. Os filtros opcionais `delivered_from`, `delivered_to` e `service_id` selecionam o periodo de entrega e o tipo de servico; sem filtros, o resultado e geral. Quando nao ha ordens elegiveis, as medias sao `null`.

## Progresso

O acompanhamento detalhado das etapas esta em `docs/project-progress.md`.

## Entrega final

O documento final esta em [docs/final-delivery.pdf](docs/final-delivery.pdf). Sua fonte renderizavel esta em `docs/final-delivery.html`.

A documentacao DDD indicada na entrega esta reunida em [docs/ddd](docs/ddd), com Linguagem Ubiqua, diagramas e Event Storming alinhados ao codigo implementado.
