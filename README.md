# Tech-Challenge-Oficina

Back-end do Sistema Integrado de Atendimento e Execucao de Servicos para uma oficina mecanica.

Autor: Bruno da Silva Saran.

## Estado atual

O Dia 2 fornece a base executavel e a arquitetura em camadas, com grupos de rotas da API, formato consistente de erros e especificacao OpenAPI inicial. Autenticacao, dominio, CRUDs e ordens de servico ainda nao foram implementados.

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

## Execucao

```bash
docker compose up -d
docker compose exec app php artisan migrate
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

## Encerramento

```bash
docker compose down
```

Para remover tambem o volume local do banco:

```bash
docker compose down -v
```

## PostgreSQL

PostgreSQL foi escolhido como banco unico da aplicacao e dos testes de integracao por oferecer integridade relacional, transacoes ACID, constraints, controle de concorrencia, representacao monetaria exata e suporte consistente a consultas agregadas.

## Arquitetura

O monolito utiliza DDD pragmatico com as camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP. As regras de dependencia e as convencoes estao descritas em `docs/architecture.md`.

As futuras rotas administrativas e do cliente estao separadas sob `/api/admin` e `/api/client`. Nenhum endpoint de dominio foi publicado nesta etapa.

## OpenAPI

A especificacao inicial esta em `docs/openapi.yaml`. Ela possui somente os metadados e o schema de erro realmente disponiveis; os endpoints serao documentados de forma incremental quando forem implementados.

## Progresso

O acompanhamento detalhado das etapas esta em `docs/project-progress.md`.
