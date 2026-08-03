# Arquitetura do projeto

## Abordagem

O Tech-Challenge-Oficina utiliza um monolito Laravel organizado por DDD pragmatico e arquitetura em camadas. A separacao existe para manter as regras de negocio independentes do transporte HTTP e permitir que futuros controllers, inclusive controllers Inertia, reutilizem os mesmos casos de uso.

## Camadas

### Dominio

Local previsto: `app/Domain`.

Contem entidades, objetos de valor, enums, regras de transicao, calculos e excecoes de negocio. Nao depende de Laravel, Eloquent, HTTP ou formatos de resposta.

### Aplicacao

Local previsto: `app/Application`.

Contem casos de uso, comandos e objetos de entrada e saida. Coordena dominio e persistencia por contratos definidos na fronteira da aplicacao. Nao conhece Request, JsonResponse ou Inertia.

### Infraestrutura

Local previsto: `app/Infrastructure`.

Contem implementacoes tecnicas, incluindo persistencia Eloquent, PostgreSQL e JWT. Implementa os contratos exigidos pelas camadas internas e pode depender do framework.

### Interface HTTP

Local: `app/Interfaces/Http`.

Contem controllers, Form Requests, API Resources e respostas HTTP. Converte a entrada HTTP para os casos de uso e transforma suas saidas em respostas da API. Controllers devem permanecer pequenos e sem regras de negocio.

## Direcao das dependencias

As dependencias seguem o sentido `Interface HTTP -> Aplicacao -> Dominio`. A Infraestrutura fornece implementacoes para as fronteiras internas e e conectada pelo container do Laravel. O Dominio nao depende das demais camadas.

Eloquent permanece na Infraestrutura. Form Requests e API Resources permanecem na Interface HTTP. Casos de uso nao retornam respostas JSON e podem ser chamados por qualquer adaptador de interface.

## Rotas

As rotas da API possuem o prefixo global `/api`.

- `routes/api/admin.php`: rotas administrativas protegidas por JWT.
- `routes/api/client.php`: futuras rotas de acompanhamento protegidas pelo mecanismo do cliente.

Os arquivos estao carregados e agrupados sob `/api/admin` e `/api/client`. Somente funcionalidades concluidas publicam endpoints.

## Ciclo da Ordem de Servico

A Ordem de Servico nasce em `received` e seu estado somente muda por operacoes explicitas do dominio. O fluxo principal e `received` para `in_diagnosis`, `awaiting_approval`, `in_execution`, `finalized` e `delivered`. O estado terminal `cancelled` e permitido somente a partir de `received`, `in_diagnosis` ou `awaiting_approval`.

Cada transicao preserva seu instante proprio. A tabela `service_orders` mantem os vinculos obrigatorios com cliente e veiculo, o estado atual e os instantes de recebimento, inicio do diagnostico, disponibilizacao do orcamento, inicio da execucao, finalizacao, entrega e cancelamento. O dominio nao oferece alteracao generica de estado.

O caso de uso `StartServiceOrderDiagnosis` localiza a OS, solicita ao agregado a transicao de `received` para `in_diagnosis` e persiste `diagnosis_started_at`. A interface HTTP apenas aciona esse caso de uso; repeticoes e estados incompativeis sao rejeitados pelo dominio como conflito.

O caso de uso `CompleteServiceOrderDiagnosis` conclui o diagnostico somente quando a OS esta em `in_diagnosis` e possui ao menos um servico. A composicao e os snapshots persistidos formam o orcamento disponibilizado pela API. A transicao para `awaiting_approval` registra `awaiting_approval_at`; nenhuma notificacao externa ou aprovacao e executada nesta acao.

O acompanhamento do cliente combina CPF ou CNPJ normalizado com um token aleatorio especifico da OS. A aplicacao persiste somente o hash SHA-256 do token e devolve o valor original apenas na criacao administrativa. A consulta publica usa POST, responde 404 para qualquer combinacao incorreta e possui Resource proprio que omite cliente, veiculo e token.

Enquanto a OS esta em `awaiting_approval`, reparos adicionais podem associar novos servicos com snapshots dos valores vigentes e recalculo do total. A aprovacao explicita muda para `in_execution`, registra o instante e consome o estoque associado. O cancelamento continua limitado aos estados anteriores a execucao.

A composicao inicial e coordenada por um caso de uso da camada de Aplicacao. Ele normaliza e valida o CPF ou CNPJ pelo objeto de valor do dominio, localiza o cliente, confirma a propriedade do veiculo e captura o valor vigente de cada servico. A Ordem de Servico e seus itens sao persistidos juntos em transacao. O item mantem quantidade e snapshot do valor unitario, sem depender do preco atual do catalogo para reconstruir o historico.

A criacao completa adiciona pecas e insumos com snapshot de tipo e valor unitario. O agregado calcula subtotais e total em centavos inteiros, e o repositorio persiste a OS e toda a composicao em uma unica transacao. O estoque nao e alterado na criacao.

A aprovacao e executada por uma operacao atomica do repositorio. Ela bloqueia primeiro a OS e depois os itens de estoque em ordem deterministica, valida todos os saldos antes de qualquer alteracao, registra as baixas vinculadas a OS e persiste a entrada em `in_execution` na mesma transacao. A restricao unica por OS e item complementa a validacao de estado contra baixas duplicadas. Qualquer insuficiencia reverte saldos, movimentos e transicao.

Os casos de uso `FinalizeServiceOrder` e `DeliverServiceOrder` concluem o ciclo por transicoes explicitas de `in_execution` para `finalized` e de `finalized` para `delivered`. A listagem paginada e o detalhamento reconstituem o agregado com servicos, pecas, insumos, snapshots e instantes. Nao existe endpoint de atualizacao generica da OS.

O caso de uso `GetServiceOrderExecutionTimeMetrics` consulta somente OS entregues, opcionalmente filtradas pelo periodo de `delivered_at` e pelo servico associado. O dominio calcula o ciclo completo entre recebimento e entrega e cada intervalo entre estados consecutivos. OS incompletas e canceladas nao entram na media. O contrato apresenta duracoes inteiras em segundos e quantidade de ordens elegiveis; a ausencia de amostra produz medias nulas.

## Erros da API

Erros da API utilizam um envelope consistente:

```json
{
    "error": {
        "code": "not_found",
        "message": "Not Found",
        "details": {}
    }
}
```

`details` e omitido quando nao ha informacoes adicionais. Erros de validacao usam `validation_error`, falhas de autenticacao usam `unauthenticated` e respostas HTTP conhecidas possuem codigos estaveis.

## Documentacao executavel da API

O contrato OpenAPI 3.1 permanece desacoplado do codigo PHP em `docs/openapi.yaml`, sem DocBlocks ou anotacoes. A rota `/docs/openapi.yaml` serve esse arquivo e `/docs` renderiza o Swagger UI 5.32.1. O visualizador e exclusivamente documental e usa `/api` como base para executar as operacoes descritas.

## Documentacao DDD

A Linguagem Ubiqua esta versionada em `docs/ddd/ubiquitous-language.md`. Os diagramas de Contexto Estrategico, Agregados, Classes de Dominio, Sequencia dos fluxos principais e transicoes da OS estao em `docs/ddd/diagrams.md`, escritos em Mermaid e limitados aos componentes implementados.

## Convencoes

- Identificadores do codigo e contratos da API sao escritos em ingles.
- Documentacao de entrega e mensagens destinadas ao usuario sao escritas em portugues do Brasil.
- Valores monetarios usam representacao exata e nunca `float`.

## Cobertura dos dominios criticos

A configuracao `phpunit.domain.xml` mede separadamente as oito classes que concentram as regras criticas de CPF/CNPJ, placa, estoque nao negativo, valor monetario exato, composicao e snapshots da OS, transicoes de estado e tempo medio. A imagem Docker fornece PCOV 1.0.12, mantendo a medicao reproduzivel sem misturar controllers e infraestrutura no percentual de dominio.

A configuracao `phpunit.integration.xml` executa as suites Unit e Feature contra PostgreSQL e mede o mesmo recorte critico durante os fluxos HTTP e de persistencia. O teste `CompleteServiceOrderFlowTest` atravessa a API desde os cadastros ate a entrega, incluindo orçamento calculado pelo servidor, acompanhamento do cliente, aprovação, consumo de estoque e consulta da media.
- Regras de negocio sao testadas prioritariamente como testes unitarios de Dominio.
- Fluxos HTTP e persistencia PostgreSQL sao cobertos por testes de integracao.
- Novas classes e diretorios sao criados somente quando houver comportamento concreto.
