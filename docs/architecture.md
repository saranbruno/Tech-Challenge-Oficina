# Arquitetura do projeto

## Abordagem

O Tech-Challenge-Oficina utiliza um monolito Laravel organizado por DDD pragmatico e arquitetura em camadas. A separacao existe para manter as regras de negocio independentes do transporte HTTP e permitir que qualquer adaptador de entrada reutilize os mesmos casos de uso. A Fase 2 adota Clean Architecture como regra explicita, sem criar frontend, microservicos ou novos processos de execucao.

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

### Regras objetivas

| Origem | Pode depender de | Nao pode depender de |
| --- | --- | --- |
| `Domain` | PHP e outros tipos de `Domain` | Laravel, Eloquent, HTTP, `Application`, `Infrastructure`, `Interfaces` e modelos de persistencia |
| `Application` | `Domain`, DTOs e contratos definidos em `Application`, PHP | helpers ou tipos do Laravel, Eloquent, HTTP, `Infrastructure`, `Interfaces` e `App\Models` |
| `Infrastructure` | contratos de `Application`, `Domain`, Laravel, Eloquent e fornecedores tecnicos | `Interfaces` e regras de apresentacao |
| `Interfaces/Http` | casos de uso, DTOs e resultados de `Application`, tipos de `Domain` quando necessarios para apresentacao e Laravel HTTP | adapters concretos de `Infrastructure` e modelos Eloquent como contrato de entrada |
| composicao do Laravel | todas as camadas necessarias para realizar bindings | regras de negocio |

As dependencias sao avaliadas tanto por imports estaticos quanto pelo tipo concreto que cruza uma fronteira em tempo de execucao. Declarar `mixed` nao torna aceitavel devolver um modelo Eloquent atraves de um contrato interno.

## Auditoria de Clean Architecture do Dia 2

### Escopo e metodo

A auditoria cobriu os 100 arquivos PHP de `app`: 20 de Dominio, 35 de Aplicacao, 14 de Infraestrutura, 29 da Interface HTTP e os dois arquivos de composicao/modelo administrativo. Foram revisados namespaces e imports, helpers de framework, contratos, tipos de retorno, DTOs, excecoes, referencias reais nos testes, tamanho das classes e fluxo concreto entre repositories, casos de uso, controllers e Resources.

### Mapa observado

| Origem | Dependencias observadas | Resultado |
| --- | --- | --- |
| `Domain` | somente tipos do proprio Dominio e PHP, incluindo `DateTimeImmutable` e `DomainException` | Conforme; nenhuma dependencia proibida encontrada |
| `Application` | contratos e DTOs proprios, `Domain`, quatro chamadas a `config()` e uma referencia a `App\Models\User` | Duas violacoes de direcao na autenticacao |
| `Infrastructure` | contratos de `Application`, tipos de `Domain`, Eloquent, PostgreSQL e JWT | Direcao estatica conforme |
| `Interfaces/Http` | casos de uso, DTOs, tipos de `Domain`, Requests, Resources e respostas Laravel | Sem import direto de `Infrastructure`, mas com modelos Eloquent recebidos por contratos `mixed` |
| `AppServiceProvider` | contratos internos e adapters concretos | Conforme como composition root |

### Achados e destino obrigatorio

| ID | Evidencia | Impacto | Destino |
| --- | --- | --- | --- |
| `ARQ-01` | `AdminTokenProvider::user()` retorna `App\Models\User`; `AdminAuthController::me()` chama metodos e propriedades desse modelo. | A Aplicacao conhece um modelo Eloquent e a Interface depende do formato concreto da persistencia. | Dia 3, item `D3-AUTH` |
| `ARQ-02` | `LoginAdmin` e `RefreshAdminToken` chamam `config()` quatro vezes. | Casos de uso dependem do container/configuracao global do Laravel. | Dia 3, item `D3-AUTH` |
| `ARQ-03` | `CustomerService`, `VehicleService`, `ServiceService` e `InventoryService` concentram 22 operacoes publicas independentes de listar, consultar, criar, atualizar, ajustar e excluir. | Casos de uso possuem responsabilidade e dependencias agrupadas por recurso, dificultando evolucao e testes isolados. | Dia 3, item `D3-USE-CASES` |
| `ARQ-04` | `CreateInitialServiceOrder` duplica parte de `CreateServiceOrder` e so e referenciado por seu teste legado. | Existem dois caminhos de criacao com validacoes e capacidades divergentes. | Dia 3, item `D3-SERVICE-ORDER` |
| `ARQ-05` | Entradas de composicao e o resultado de metricas usam arrays genericos; tres casos de uso validam tipos por `instanceof` durante a execucao e lancam `DomainException` generica. | A assinatura nao expressa integralmente o contrato e permite erro de programacao chegar como erro de dominio. | Dia 3, item `D3-CONTRACTS` |
| `ARQ-06` | Seis metodos de repository, seis metodos de Aplicacao e seis implementacoes Eloquent declaram retorno `mixed`. | Ha 18 assinaturas sem tipo concreto nas fronteiras internas. | Dia 4, item `D4-PAGINATION` |
| `ARQ-07` | As listagens de cliente, veiculo, servico e estoque devolvem paginadores com modelos Eloquent; quatro Resources aceitam alternativamente entidade ou modelo, e `StockMovementResource` recebe somente `StockMovementModel`. | Eloquent atravessa Aplicacao e chega a Interface em tempo de execucao apesar de nao existir import estatico. | Dia 4, item `D4-MAPPING` |
| `ARQ-08` | `EloquentServiceOrderRepository` possui 242 linhas e sete operacoes publicas, reunindo listagem, metricas, tracking, criacao, atualizacao, aprovacao com estoque e reconstituicao. | Consultas, comandos transacionais e mapeamento mudam pela mesma classe e pelo mesmo contrato. | Dia 4, item `D4-SERVICE-ORDER-PERSISTENCE` |
| `ARQ-09` | Os contratos `findOrFail` nao declaram excecao interna e as implementacoes deixam `ModelNotFoundException` atravessar a Aplicacao. | O comportamento de falha dos ports depende implicitamente do Eloquent e do handler HTTP do Laravel. | Dia 4, item `D4-ERRORS` |
| `ARQ-10` | Nao existe `tests/Architecture`; as regras atuais dependem apenas de revisao humana. | Uma dependencia proibida pode ser adicionada sem falha automatica. | Dia 4, item `D4-ARCH-TESTS` |

### Classes grandes mantidas

`ServiceOrder` possui 204 linhas, mas permanece coeso como raiz do agregado que protege composicao, total, status e instantes. Dividi-lo agora separaria invariantes que mudam juntas. `ServiceOrderController` possui 142 linhas e dez dependencias, mas seus metodos apenas convertem HTTP, acionam um caso de uso e apresentam a resposta; nao foi encontrada regra de negocio no controller. Esses dois tamanhos foram revisados e nao geram refatoracao cosmetica nos Dias 3 ou 4.

## Arquitetura-alvo apos os Dias 3 e 4

A arquitetura-alvo preserva os mesmos quatro limites e remove todas as excecoes observadas:

1. HTTP converte Request em DTO de Aplicacao e converte resultado interno em Resource ou `JsonResponse`.
2. Cada caso de uso independente possui uma classe coesa e depende somente de ports internos e do Dominio.
3. Resultados de autenticacao, paginacao, metricas e movimentacao pertencem a Aplicacao ou Dominio e possuem tipos concretos.
4. Repositories e queries nunca devolvem Eloquent para dentro; adapters convertem modelos em entidades ou DTOs antes do retorno.
5. Consultas operacionais e de metricas da OS ficam separadas dos comandos transacionais e do mapeamento de persistencia.
6. O composition root do Laravel liga ports a adapters; nenhuma camada interna consulta configuracao global.
7. Testes arquiteturais impedem imports e helpers proibidos e recusam `mixed` nos contratos internos.

### Resultado da refatoracao do Dia 3

Os quatro itens do Dia 3 foram concluidos sem alterar rotas ou payloads HTTP. A autenticacao agora usa `TokenData` e `AuthenticatedAdminData` como contratos internos; somente o adapter JWT consulta configuracao Laravel e converte `User`. As 22 operacoes antes reunidas em quatro servicos CRUD possuem casos de uso independentes, enquanto factories de Aplicacao concentram a construcao e a validacao compartilhada.

`CreateServiceOrder` tornou-se o unico fluxo de abertura. A cobertura valida do caso legado foi portada com uma colecao vazia de itens de estoque, e `CreateInitialServiceOrder` foi removido. `RequestedServiceCollection` e `RequestedInventoryItemCollection` tornam as composicoes explicitas nas assinaturas; entradas vazias usam as excecoes nomeadas `InvalidServiceOrderBudget` e `InvalidAdditionalRepair`. O calculador retorna `ServiceOrderExecutionTimeMetrics`, e a Interface HTTP mantem o formato publico em `snake_case`.

As buscas apos a refatoracao encontraram zero uso de `config()` e zero import de `App\Models` nos 60 arquivos da Aplicacao. Permanecem 12 assinaturas `mixed`, todas pertencentes ao escopo deliberado de paginacao e mapeamento do Dia 4.

### Backlog fechado do Dia 3, concluido

| Item | Alteracao exata | Criterio de aceite |
| --- | --- | --- |
| `D3-AUTH` | Fazer o provider devolver dados internos concretos de token e identidade administrativa; criar caso de uso para o administrador autenticado; retirar `App\Models\User` do contrato e `config()` dos casos de uso. | Nenhum arquivo de `Application` importa `App\Models` ou chama helper Laravel; login, refresh e `me` preservam o contrato HTTP. |
| `D3-USE-CASES` | Substituir os quatro servicos CRUD por casos de uso separados: listar, consultar, criar, atualizar e excluir para clientes, veiculos e servicos; listar, consultar, criar, atualizar, ajustar estoque, listar movimentos e excluir para estoque. | As 22 operacoes independentes possuem classes coesas; validacao comum nao e duplicada; controllers continuam finos. |
| `D3-SERVICE-ORDER` | Remover o caminho legado `CreateInitialServiceOrder` e portar sua cobertura valida para `CreateServiceOrder` com lista vazia de itens de estoque. | Existe um unico caso de uso de abertura e nenhum comportamento publico da Fase 1 e perdido. |
| `D3-CONTRACTS` | Introduzir entradas tipadas para composicoes e um resultado concreto para metricas; substituir erros genericos de composicao por excecoes de negocio nomeadas quando fizerem parte do contrato. | Casos de uso nao recebem colecoes sem contrato nem retornam array de formato implicito; responses HTTP permanecem identicas. |

### Backlog fechado do Dia 4

| Item | Alteracao exata | Criterio de aceite |
| --- | --- | --- |
| `D4-PAGINATION` | Criar resultado paginado interno com itens e metadados; tipar os seis ports e seus consumidores hoje declarados como `mixed`. | Zero retorno `mixed` em `Domain` e `Application`; formato e paginacao HTTP preservados. |
| `D4-MAPPING` | Converter toda listagem Eloquent em entidades ou DTOs antes de sair da Infraestrutura; criar DTO de movimentacao de estoque; tornar cada Resource dependente de um unico tipo interno. | Nenhum modelo Eloquent cruza um contrato de Aplicacao nem e recebido por Resource. |
| `D4-SERVICE-ORDER-PERSISTENCE` | Separar repository do agregado, query de listagem/tracking, query de metricas e comando atomico de aprovacao; extrair o mapeamento da OS para uma responsabilidade propria da Infraestrutura. | Cada port possui um motivo coeso para mudar; criacao, tracking, metricas, paginacao, aprovacao e estoque preservam os resultados atuais. |
| `D4-ERRORS` | Traduzir ausencias da persistencia para excecao interna estavel e manter o mapeamento HTTP 404 na Interface. | Nenhum contrato interno depende implicitamente de `ModelNotFoundException`; todos os testes de not found continuam aprovados. |
| `D4-ARCH-TESTS` | Criar testes em `tests/Architecture` para imports entre camadas, helpers Laravel nas camadas internas, Eloquent fora da Infraestrutura e `mixed` nos contratos. Usar fixture controlada para provar que a regra detecta violacao. | A fixture proibida falha na verificacao, o codigo real passa e a suite funcional permanece verde sem nova biblioteca. |

### Resultado da refatoracao do Dia 4

Os cinco itens do backlog foram concluidos sem alterar rotas, status HTTP ou formatos de resposta. `PaginatedResult` substitui os retornos `mixed`; a Interface reconstrói o paginador Laravel apenas na borda HTTP. Repositories Eloquent convertem listagens em entidades ou em `StockMovementData`, e cada Resource recebe um unico tipo interno.

A persistencia de OS foi dividida entre `ServiceOrderRepository`, `ServiceOrderQuery`, `ServiceOrderMetricsQuery` e `ServiceOrderApproval`. `ServiceOrderMapper` concentra a traducao entre o agregado e os modelos, enquanto a aprovacao continua transacional, com bloqueio da OS e do estoque. Ausencias agora geram `ResourceNotFound`, traduzida pela Interface para o contrato HTTP 404 existente.

O modelo `User` foi movido para a Infraestrutura. Cinco testes em `tests/Architecture` verificam as dependencias de Dominio e Aplicacao, Eloquent fora da Infraestrutura, persistencia na Interface e tipos `mixed`; a fixture controlada confirma que dependência de Infraestrutura, helper Laravel e `mixed` sao detectados. As buscas no codigo real retornam zero violacao e a suite integrada permanece aprovada.

### Nucleo de notificacoes do Dia 6

`DispatchServiceOrderStatusNotification` pertence a Aplicacao e depende somente das portas `EmailNotificationSender`, `SmsNotificationSender` e `NotificationFailureReporter`. Ele seleciona todos os contatos disponiveis, cria uma mensagem minima por status e isola a falha de cada tentativa. O Dominio continua sem conhecer mensagens, fornecedores ou mecanismos de entrega.

`LoggingNotificationFailureReporter` implementa na Infraestrutura o registro sanitizado das falhas. `LaravelEmailNotificationSender` e `ServiceOrderStatusMail` usam o mailer Laravel configurado por ambiente; o Compose fornece Mailpit local. O adapter SMS e a ligacao do dispatcher aos casos de uso de transicao permanecem nos Dias 8 e 9.

## Rotas

As rotas da API possuem o prefixo global `/api`.

- `routes/api/admin.php`: rotas administrativas protegidas por JWT.
- `routes/api/client.php`: rotas de acompanhamento e aprovacao protegidas por documento e token da OS.

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
- Helpers e configuracoes globais do Laravel nao sao usados em Dominio ou Aplicacao.
- Ports internos possuem retornos concretos; `mixed` nao e usado para ocultar tipos de framework.
- Modelos Eloquent sao convertidos dentro da Infraestrutura antes de cruzar uma fronteira interna.

## Cobertura dos dominios criticos

A configuracao `phpunit.domain.xml` mede separadamente as classes que concentram as regras criticas de CPF/CNPJ, e-mail, telefone E.164, placa, estoque nao negativo, valor monetario exato, composicao e snapshots da OS, transicoes de estado e tempo medio. A imagem Docker fornece PCOV 1.0.12, mantendo a medicao reproduzivel sem misturar controllers e infraestrutura no percentual de dominio.

A configuracao `phpunit.integration.xml` executa as suites Unit e Feature contra PostgreSQL e mede o mesmo recorte critico durante os fluxos HTTP e de persistencia. O teste `CompleteServiceOrderFlowTest` atravessa a API desde os cadastros ate a entrega, incluindo orçamento calculado pelo servidor, acompanhamento do cliente, aprovação, consumo de estoque e consulta da media.
- Regras de negocio sao testadas prioritariamente como testes unitarios de Dominio.
- Fluxos HTTP e persistencia PostgreSQL sao cobertos por testes de integracao.
- Novas classes e diretorios sao criados somente quando houver comportamento concreto.
- Operacoes independentes de Aplicacao usam uma classe de caso de uso e o metodo `execute`; factories coesas concentram somente construcao, normalizacao e validacao compartilhadas.
- Colecoes de entrada e resultados compostos possuem tipos internos explicitos; a Interface HTTP e responsavel por preservar nomes e formatos do contrato publico.
