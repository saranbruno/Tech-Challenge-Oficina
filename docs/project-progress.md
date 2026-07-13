# Progresso do projeto

## Identificacao

- Projeto: Tech-Challenge-Oficina
- Autor: Bruno da Silva Saran
- Dia atual: 1
- Estado atual: Concluído

## Roadmap

| Dia | Etapa | Estado | Inicio | Conclusao |
| --- | --- | --- | --- | --- |
| 1 | Base executavel e ambiente local | Concluído | 2026-07-13 | 2026-07-13 |
| 2 | Arquitetura e convencoes do projeto | Pendente |  |  |
| 3 | Autenticacao administrativa JWT | Pendente |  |  |
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

## Duvidas e bloqueios

Nenhum bloqueio funcional para o Dia 1.

## Pendencias tecnicas

- Selecionar o pacote JWT recomendado no Dia 3.
- Definir os campos minimos concretos do cliente no Dia 4.
- Selecionar a ferramenta de scan no Dia 23.
- Criar o link da documentacao quando ela existir.
- Confirmar novamente qualquer push ou alteracao externa antes de executar a acao.

## Escopo previsto para o proximo dia

Dia 2: estruturar as camadas de Dominio, Aplicacao, Infraestrutura e Interface HTTP; documentar dependencias; configurar rotas sem endpoints ficticios; padronizar erros; iniciar OpenAPI; validar autoload, inicializacao e testes basicos.
