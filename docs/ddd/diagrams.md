# Diagramas DDD

## Escopo

Os diagramas representam o monólito implementado e usam os identificadores reais do código. As divisões abaixo são limites conceituais internos, não microserviços. Os eventos, políticas, atores, read models, hotspots e fluxos de erro estão detalhados no [Event Storming](event-storming.md).

## Componentes e dependencias apos o Dia 4

As setas representam as dependencias conformes encontradas no codigo. Os Dias 3 e 4 removeram configuracao Laravel, modelos Eloquent e paginadores das fronteiras internas. A Interface HTTP recebe somente entidades, DTOs e resultados da Aplicacao; a Infraestrutura concentra os modelos Eloquent, inclusive o usuario administrativo.

```mermaid
flowchart LR
    HTTP[Interface HTTP]
    Application[Aplicacao]
    Domain[Dominio]
    Infrastructure[Infraestrutura]
    Composition[Composition root Laravel]
    Laravel[Laravel e configuracao]
    Eloquent[Eloquent e PostgreSQL]

    HTTP --> Application
    HTTP --> Domain
    Application --> Domain
    Infrastructure --> Application
    Infrastructure --> Domain
    Infrastructure --> Eloquent
    Composition --> Application
    Composition --> Infrastructure
    HTTP --> Laravel
```

O Dominio nao possui dependencia proibida. A composicao do Laravel e o ponto autorizado a conhecer ports e adapters concretos. Testes em `tests/Architecture` protegem automaticamente a direcao das dependencias, a ausencia de helpers Laravel nas camadas internas, a localizacao do Eloquent e a proibicao de `mixed` em Dominio e Aplicacao.

## Contexto Estratégico

```mermaid
flowchart LR
    Admin[Administrador]
    Client[Cliente]

    subgraph Monolith[Tech-Challenge-Oficina]
        IAM[Identidade Administrativa]
        Registration[Cadastro de Atendimento]
        Catalog[Catálogo e Estoque]
        Workshop[Atendimento da Oficina]
        Reporting[Monitoramento]
    end

    PostgreSQL[(PostgreSQL)]

    Admin -->|login e JWT| IAM
    Admin -->|clientes e veículos| Registration
    Admin -->|serviços, peças, insumos e ajustes| Catalog
    Admin -->|criação, diagnóstico, finalização e entrega| Workshop
    Admin -->|consulta de duração| Reporting
    Client -->|documento e token| Workshop
    Registration -->|cliente e veículo válidos| Workshop
    Catalog -->|preços e disponibilidade| Workshop
    Workshop -->|OS entregues e instantes| Reporting
    IAM --> PostgreSQL
    Registration --> PostgreSQL
    Catalog --> PostgreSQL
    Workshop --> PostgreSQL
    Reporting --> PostgreSQL
```

| Contexto conceitual | Responsabilidade | Principais identificadores |
| --- | --- | --- |
| Identidade Administrativa | Autenticar o Administrador e emitir ou renovar JWT. | `LoginAdmin`, `RefreshAdminToken`, `AdminTokenProvider` |
| Cadastro de Atendimento | Manter Clientes e Veículos e proteger sua relação de propriedade. | `Customer`, `Document`, `Email`, `Phone`, `Vehicle`, `LicensePlate` |
| Catálogo e Estoque | Manter Serviços, Peças, Insumos, saldos e movimentações. | `Service`, `InventoryItem`, `StockQuantity`, `StockMovementData` |
| Atendimento da Oficina | Coordenar a OS, orçamento, acompanhamento, aprovação e ciclo operacional. | `ServiceOrder`, casos de uso em `Application/ServiceOrder` |
| Comunicacao de Status | Preparar mensagens minimas e tentar todos os contatos disponiveis sem acoplar o Dominio a fornecedores. | `DispatchServiceOrderStatusNotification`, ports em `Application/Notification` |
| Monitoramento | Calcular duração total e por estado das OS elegíveis. | `GetServiceOrderExecutionTimeMetrics`, `ServiceOrderExecutionTimeCalculator` |

O núcleo interno de notificações e os adapters de e-mail e SMS existem sem conexão às transições. O Compose fornece Mailpit local; SMTP de produção e Twilio permanecem configuráveis por ambiente. O SMS local usa adapter de log e nenhum segredo aparece no código. Ainda não há filas, pagamentos ou tempo real. A API REST lê o estado persistido e o PostgreSQL é o único banco da aplicação.

## Agregados e relações

```mermaid
flowchart TB
    CustomerRoot["Customer<br/>raiz de agregado"]
    Document["Document<br/>objeto de valor"]
    Email["Email<br/>objeto de valor opcional"]
    Phone["Phone<br/>objeto de valor opcional"]
    VehicleRoot["Vehicle<br/>raiz de agregado"]
    Plate["LicensePlate<br/>objeto de valor"]
    ServiceRoot["Service<br/>raiz de agregado"]
    InventoryRoot["InventoryItem<br/>raiz de agregado"]
    Stock["StockQuantity<br/>objeto de valor"]
    Movement["StockMovementData<br/>resultado interno de leitura"]
    OrderRoot["ServiceOrder<br/>raiz de agregado"]
    OrderService["ServiceOrderService<br/>entidade interna"]
    OrderInventory["ServiceOrderInventoryItem<br/>entidade interna"]
    UnitPrice["UnitPrice<br/>objeto de valor"]

    CustomerRoot -->|possui| Document
    CustomerRoot -. pode possuir .-> Email
    CustomerRoot -. pode possuir .-> Phone
    VehicleRoot -->|referencia customerId| CustomerRoot
    VehicleRoot -->|possui| Plate
    ServiceRoot -->|possui| UnitPrice
    InventoryRoot -->|possui| UnitPrice
    InventoryRoot -->|possui| Stock
    InventoryRoot -->|origina| Movement
    OrderRoot -->|referencia customerId| CustomerRoot
    OrderRoot -->|referencia vehicleId| VehicleRoot
    OrderRoot -->|contém 1..n| OrderService
    OrderRoot -->|contém 0..n| OrderInventory
    OrderService -->|referencia serviceId| ServiceRoot
    OrderService -->|preserva preço| UnitPrice
    OrderInventory -->|referencia inventoryItemId| InventoryRoot
    OrderInventory -->|preserva preço e tipo| UnitPrice
    OrderRoot -->|pode originar consumo| Movement
```

### Limites de consistência

- `ServiceOrder` controla composição, total, status e instantes do ciclo de vida.
- `Customer`, `Vehicle`, `Service` e `InventoryItem` são persistidos separadamente e referenciados por identidade na OS.
- `ServiceOrderService` e `ServiceOrderInventoryItem` pertencem à composição histórica da OS e não substituem os itens dos catálogos.
- A aprovação exige consistência entre `ServiceOrder`, saldos de `InventoryItem` e movimentações. O repositório Eloquent impõe essa operação como uma única transação PostgreSQL com bloqueio de linhas.
- `StockMovementModel` é um registro de persistência imutável; não existe entidade de domínio independente para editar movimentações.

## Classes de Domínio

```mermaid
classDiagram
    class Customer {
        +int id
        +string name
        +Document document
        +Email email
        +Phone phone
    }
    class Document {
        +string value
        +DocumentType type
    }
    class DocumentType {
        <<enumeration>>
        Cpf
        Cnpj
    }
    class Email {
        +string value
    }
    class Phone {
        +string value
    }
    class Vehicle {
        +int id
        +int customerId
        +LicensePlate licensePlate
        +string brand
        +string model
        +int year
    }
    class LicensePlate {
        +string value
    }
    class Service {
        +int id
        +string name
        +UnitPrice unitPrice
    }
    class InventoryItem {
        +int id
        +string name
        +InventoryItemType type
        +UnitPrice unitPrice
        +StockQuantity quantityAvailable
    }
    class InventoryItemType {
        <<enumeration>>
        Part
        Supply
    }
    class StockQuantity {
        +int value
    }
    class UnitPrice {
        +int cents
    }
    class ServiceOrder {
        -ServiceOrderService[] services
        -ServiceOrderInventoryItem[] inventoryItems
        +int id
        +int customerId
        +int vehicleId
        +ServiceOrderStatus status
        +receive()
        +addService()
        +addAdditionalService()
        +addInventoryItem()
        +totalAmount() int
        +startDiagnosis()
        +makeBudgetAvailable()
        +approveBudget()
        +finalize()
        +deliver()
        +cancel()
    }
    class ServiceOrderService {
        +int serviceId
        +int quantity
        +UnitPrice unitPriceSnapshot
        +subtotal() int
    }
    class ServiceOrderInventoryItem {
        +int inventoryItemId
        +InventoryItemType typeSnapshot
        +int quantity
        +UnitPrice unitPriceSnapshot
        +subtotal() int
    }
    class ServiceOrderStatus {
        <<enumeration>>
        Received
        InDiagnosis
        AwaitingApproval
        InExecution
        Finalized
        Delivered
        Cancelled
    }
    class ServiceOrderExecutionTimeCalculator {
        +calculate(ServiceOrder[]) ServiceOrderExecutionTimeMetrics
    }
    class ServiceOrderExecutionTimeMetrics

    Customer *-- Document
    Customer o-- Email
    Customer o-- Phone
    Document --> DocumentType
    Vehicle *-- LicensePlate
    Vehicle --> Customer : customerId
    Service *-- UnitPrice
    InventoryItem *-- UnitPrice
    InventoryItem *-- StockQuantity
    InventoryItem --> InventoryItemType
    ServiceOrder *-- ServiceOrderService
    ServiceOrder *-- ServiceOrderInventoryItem
    ServiceOrder --> ServiceOrderStatus
    ServiceOrder --> Customer : customerId
    ServiceOrder --> Vehicle : vehicleId
    ServiceOrderService --> Service : serviceId
    ServiceOrderService *-- UnitPrice
    ServiceOrderInventoryItem --> InventoryItem : inventoryItemId
    ServiceOrderInventoryItem --> InventoryItemType
    ServiceOrderInventoryItem *-- UnitPrice
    ServiceOrderExecutionTimeCalculator ..> ServiceOrder
    ServiceOrderExecutionTimeCalculator --> ServiceOrderExecutionTimeMetrics
```

## Sequência: criação e disponibilização do orçamento

```mermaid
sequenceDiagram
    actor Admin as Administrador
    participant HTTP as ServiceOrderController
    participant Creation as CreateServiceOrder
    participant CustomerRepo as CustomerRepository
    participant VehicleRepo as VehicleRepository
    participant Catalog as Service e Inventory repositories
    participant Order as ServiceOrder
    participant OrderRepo as ServiceOrderRepository
    participant Start as StartServiceOrderDiagnosis
    participant Complete as CompleteServiceOrderDiagnosis

    Admin->>HTTP: POST /api/admin/service-orders com JWT
    HTTP->>Creation: execute(document, vehicle, services, inventoryItems)
    Creation->>CustomerRepo: findByDocumentOrFail(document)
    Creation->>VehicleRepo: findOrFail(vehicleId)
    Creation->>Creation: validar propriedade do veículo
    Creation->>Catalog: localizar preços e tipos atuais
    Creation->>Order: receive(customerId, vehicleId, tokenHash)
    Creation->>Order: addService e addInventoryItem com snapshots
    Creation->>Order: totalAmount()
    Creation->>OrderRepo: create(Order)
    OrderRepo-->>Creation: OS persistida em transação
    Creation-->>HTTP: OS e token original
    HTTP-->>Admin: 201 com orçamento calculado

    Admin->>HTTP: POST /diagnosis/start
    HTTP->>Start: execute(id, occurredAt)
    Start->>OrderRepo: findOrFail(id)
    Start->>Order: startDiagnosis(occurredAt)
    Start->>OrderRepo: update(Order)
    Admin->>HTTP: POST /diagnosis/complete
    HTTP->>Complete: execute(id, occurredAt)
    Complete->>OrderRepo: findOrFail(id)
    Complete->>Order: makeBudgetAvailable(occurredAt)
    Complete->>OrderRepo: update(Order)
    HTTP-->>Admin: OS em awaiting_approval
```

## Sequência: acompanhamento e aprovação com consumo de estoque

```mermaid
sequenceDiagram
    actor Client as Cliente
    participant HTTP as ClientServiceOrderController
    participant Track as TrackServiceOrder
    participant Approve as ApproveClientServiceOrderBudget
    participant Repo as EloquentServiceOrderRepository
    participant Order as ServiceOrder
    participant Stock as InventoryItem e stock_movements

    Client->>HTTP: POST /api/client/service-orders/tracking
    HTTP->>Track: execute(document, trackingToken)
    Track->>Repo: findForClientOrFail(document, SHA-256 token)
    alt combinação inválida
        Repo-->>HTTP: não encontrado
        HTTP-->>Client: 404 sem revelar qual dado falhou
    else combinação válida
        Repo-->>Track: ServiceOrder
        Track-->>HTTP: dados públicos da OS
        HTTP-->>Client: 200 sem IDs administrativos ou token
    end

    Client->>HTTP: POST /api/client/service-orders/approve
    HTTP->>Approve: execute(document, trackingToken)
    Approve->>Repo: approveForClient(document, SHA-256 token, occurredAt)
    Repo->>Repo: iniciar transação e bloquear OS
    Repo->>Stock: bloquear itens em ordem determinística
    alt estoque insuficiente
        Repo->>Repo: rollback
        HTTP-->>Client: 409 insufficient_inventory_stock
    else todos os saldos disponíveis
        Repo->>Order: approveBudget(occurredAt)
        Repo->>Stock: baixar saldos e criar movimentações
        Repo->>Repo: commit
        HTTP-->>Client: 200 com OS em in_execution
    end
```

## Sequência: conclusão do ciclo e monitoramento

```mermaid
sequenceDiagram
    actor Admin as Administrador
    participant HTTP as ServiceOrderController
    participant Finalizer as FinalizeServiceOrder
    participant Delivery as DeliverServiceOrder
    participant MetricsUseCase as GetServiceOrderExecutionTimeMetrics
    participant Repo as ServiceOrderRepository
    participant Calculator as ServiceOrderExecutionTimeCalculator

    Admin->>HTTP: POST /api/admin/service-orders/{id}/finalize
    HTTP->>Finalizer: execute(id)
    Finalizer->>Repo: findOrFail(id)
    Finalizer->>Finalizer: ServiceOrder.finalize(occurredAt)
    Finalizer->>Repo: update(ServiceOrder)
    HTTP-->>Admin: 200 finalized

    Admin->>HTTP: POST /api/admin/service-orders/{id}/deliver
    HTTP->>Delivery: execute(id)
    Delivery->>Repo: findOrFail(id)
    Delivery->>Delivery: ServiceOrder.deliver(occurredAt)
    Delivery->>Repo: update(ServiceOrder)
    HTTP-->>Admin: 200 delivered

    Admin->>HTTP: GET /api/admin/service-orders-metrics/execution-time
    HTTP->>MetricsUseCase: execute(filters)
    MetricsUseCase->>Repo: completedForMetrics(filters)
    Repo-->>MetricsUseCase: OS entregues elegíveis
    MetricsUseCase->>Calculator: calculate(serviceOrders)
    Calculator-->>MetricsUseCase: médias em segundos
    HTTP-->>Admin: 200 com ciclo total e tempo por status
```

## Transições do agregado Ordem de Serviço

```mermaid
stateDiagram-v2
    [*] --> Recebida
    Recebida --> EmDiagnostico: iniciar diagnóstico
    EmDiagnostico --> AguardandoAprovacao: disponibilizar orçamento
    AguardandoAprovacao --> EmExecucao: aprovar e consumir estoque
    EmExecucao --> Finalizada: finalizar execução
    Finalizada --> Entregue: entregar veículo
    Recebida --> Cancelada: cancelar
    EmDiagnostico --> Cancelada: cancelar
    AguardandoAprovacao --> Cancelada: cancelar
    Entregue --> [*]
    Cancelada --> [*]
```
