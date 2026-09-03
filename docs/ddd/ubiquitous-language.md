# Linguagem Ubíqua

## Objetivo

Este glossário estabelece o vocabulário do domínio da oficina e seu mapeamento para os identificadores em inglês usados no código e na API. Os termos descrevem somente comportamentos implementados no monólito `Tech-Challenge-Oficina`.

## Termos do domínio

| Termo em português | Identificador no código | Definição e regras | Evitar como sinônimo |
| --- | --- | --- | --- |
| Cliente | `Customer` | Pessoa física ou jurídica atendida pela oficina. Possui nome, documento e contatos opcionais. É o proprietário dos veículos e o titular das Ordens de Serviço. | Administrador, usuário |
| Documento | `Document` | Objeto de valor que normaliza, classifica e valida um CPF ou CNPJ. Somente dígitos são persistidos e valores repetidos ou com dígitos verificadores inválidos são rejeitados. | Token, credencial |
| Contato do Cliente | `email`, `phone` | Meio opcional para notificações de status. E-mail e telefone são independentes, podem coexistir ou estar ambos ausentes; não existe canal preferencial cadastrado. | Credencial, canal obrigatório |
| E-mail | `Email`, `email` | Endereço opcional validado e normalizado em letras minúsculas. Não identifica exclusivamente o Cliente. | Administrador, documento |
| Telefone | `Phone`, `phone` | Número opcional normalizado e persistido no formato internacional E.164. Números brasileiros sem código do país recebem `+55`. Não identifica exclusivamente o Cliente. | WhatsApp, documento |
| Notificação de Status | `ServiceOrderStatusNotification` | Mensagem de melhor esforço que informa somente a identificação da OS e o novo status já persistido. | Evento de domínio, garantia de entrega |
| Meio de Entrega | `NotificationMedium` | Identificação técnica e não persistida da tentativa por e-mail ou SMS. Não representa preferência do Cliente nem cria `notification_channel`. | Preferência, canal cadastrado |
| Dispatcher de Notificação | `DispatchServiceOrderStatusNotification` | Política de Aplicação que tenta todos os contatos disponíveis de forma independente e registra falhas sem propagá-las. | Fila, fornecedor |
| CPF | `DocumentType::Cpf`, `cpf` | Documento brasileiro de pessoa física com 11 dígitos e validação dos dígitos verificadores. Não é usado isoladamente como segredo de acompanhamento. | CNPJ, token de acompanhamento |
| CNPJ | `DocumentType::Cnpj`, `cnpj` | Documento brasileiro de pessoa jurídica com 14 dígitos e validação dos dígitos verificadores. Não é usado isoladamente como segredo de acompanhamento. | CPF, token de acompanhamento |
| Veículo | `Vehicle` | Bem pertencente obrigatoriamente a um Cliente, identificado por placa única, marca, modelo e ano. Uma OS somente aceita um veículo do cliente informado. | Ordem de Serviço |
| Placa | `LicensePlate`, `license_plate` | Objeto de valor normalizado em letras maiúsculas e sem separadores. Aceita os padrões brasileiros antigo e Mercosul. | Identificador da OS |
| Serviço | `Service` | Item do catálogo que descreve uma atividade da oficina por nome e valor unitário em centavos. Pode compor uma OS com quantidade positiva. | Peça, insumo, execução |
| Peça | `InventoryItem` com `InventoryItemType::Part`, `part` | Item físico do catálogo de estoque classificado como peça, com preço unitário e saldo disponível. | Serviço, insumo |
| Insumo | `InventoryItem` com `InventoryItemType::Supply`, `supply` | Item consumível do mesmo catálogo de estoque, diferenciado pelo tipo `supply`. Segue as mesmas regras transacionais de saldo da peça. | Serviço, peça |
| Item de estoque | `InventoryItem` | Entidade do catálogo que representa uma Peça ou um Insumo. Possui nome, tipo, valor unitário e quantidade disponível não negativa. | Item de orçamento |
| Estoque | `quantityAvailable`, `StockQuantity` | Saldo inteiro disponível de cada Item de estoque. Nunca pode ser negativo e sua alteração é registrada como movimentação. | Catálogo, orçamento |
| Movimentação de estoque | `StockMovementModel`, `stock_movements` | Registro imutável da mudança de saldo, contendo variação, saldo anterior e posterior. Pode decorrer de estoque inicial, ajuste manual ou consumo por OS. | Saldo, item de estoque |
| Ajuste manual de estoque | `adjustStock`, `manual_adjustment` | Ação administrativa explícita que define um novo saldo e registra a diferença. Usa transação e bloqueio da linha do item. | Consumo da OS |
| Ordem de Serviço | `ServiceOrder`, `service_order` | Agregado central do atendimento. Relaciona Cliente, Veículo, Serviços, Peças e Insumos, preserva orçamento e instantes do ciclo de vida e controla suas próprias transições. Abreviação aceita: OS. | Serviço, orçamento |
| Serviço da Ordem de Serviço | `ServiceOrderService` | Item da composição da OS que referencia um Serviço e preserva quantidade e snapshot do valor unitário. O mesmo Serviço não pode aparecer duas vezes na OS. | Serviço do catálogo |
| Item de estoque da Ordem de Serviço | `ServiceOrderInventoryItem` | Item da composição da OS que referencia uma Peça ou Insumo e preserva tipo, quantidade e valor unitário históricos. O mesmo Item de estoque não pode aparecer duas vezes. | Item de estoque do catálogo |
| Diagnóstico | `startDiagnosis`, `makeBudgetAvailable` | Etapa iniciada por ação explícita após o recebimento. Sua conclusão valida a composição e disponibiliza o orçamento pela API. | Execução, orçamento |
| Orçamento | `totalAmount`, `total_amount` | Composição financeira calculada pelo servidor a partir dos itens da OS. Não representa cobrança ou pagamento. Exige ao menos um Serviço para ser disponibilizado. | Pagamento, fatura |
| Item de orçamento | `ServiceOrderService` ou `ServiceOrderInventoryItem` | Linha que contribui para o Orçamento por quantidade multiplicada pelo snapshot do valor unitário. | Item de catálogo |
| Snapshot de valor | `unitPriceSnapshot`, `unit_price_snapshot` | Valor unitário em centavos copiado para a composição da OS. Mudanças posteriores no catálogo não alteram o histórico do orçamento. | Preço atual do catálogo |
| Valor unitário | `UnitPrice`, `unit_price` | Objeto de valor monetário não negativo representado por inteiro em centavos, sem `float`. | Total, valor em ponto flutuante |
| Subtotal | `subtotal` | Quantidade multiplicada pelo Snapshot de valor de um item da OS. | Total geral |
| Total do orçamento | `totalAmount`, `total_amount` | Soma dos subtotais de Serviços, Peças e Insumos da OS, calculada exclusivamente pelo servidor. | Total enviado pelo cliente da API |
| Disponibilização do orçamento | `CompleteServiceOrderDiagnosis`, `makeBudgetAvailable` | Ação que conclui o Diagnóstico, valida a existência de Serviço e move a OS para Aguardando aprovação; após persistir, tenta notificar os contatos disponíveis. | Envio por e-mail obrigatório, aprovação |
| Aprovação | `ApproveClientServiceOrderBudget`, `approveBudget` | Autorização explícita feita pelo Cliente com documento e token válidos. Move a OS para Em execução e, na mesma transação, consome o estoque associado. | Autenticação administrativa, disponibilização |
| Decisão externa de orçamento | `BudgetDecision`, `ProcessServiceOrderBudgetDecision` | Decisão `approved` ou `rejected` recebida pelo webhook HMAC; a mesma decisão repetida não repete efeitos. | `budget_rejected`, versão de orçamento |
| Recusa do orçamento | `rejected`, `cancel` | Decisão externa que move a OS de Aguardando aprovação diretamente para Cancelada, sem baixa de estoque. | `budget_rejected`, retomada da OS |
| Reparo adicional | `AddAdditionalRepairs`, `addAdditionalService` | Novo Serviço incluído na mesma OS enquanto ela aguarda aprovação. Usa o preço vigente como novo snapshot e recalcula o total. | Nova OS, edição genérica do orçamento |
| Cancelamento | `CancelServiceOrder`, `cancel` | Encerramento terminal permitido apenas a partir de Recebida, Em diagnóstico ou Aguardando aprovação. Registra o instante do cancelamento. | Recusa com retomada, exclusão |
| Execução | `InExecution`, `in_execution` | Etapa iniciada automaticamente após a aprovação e o consumo transacional do estoque. | Diagnóstico, finalização |
| Finalização | `FinalizeServiceOrder`, `finalize` | Ação administrativa que conclui a execução e move a OS de Em execução para Finalizada. | Entrega |
| Entrega | `DeliverServiceOrder`, `deliver` | Ação administrativa que registra a devolução do veículo e move a OS de Finalizada para Entregue. | Finalização |
| Status da Ordem de Serviço | `ServiceOrderStatus` | Enum que restringe o estado da OS aos sete valores confirmados. O estado só muda por ações explícitas do domínio. | Campo editável livremente |
| Recebida | `Received`, `received` | Estado inicial atribuído na criação da OS. | Em diagnóstico |
| Em diagnóstico | `InDiagnosis`, `in_diagnosis` | Estado após o início explícito do Diagnóstico. | Aguardando aprovação |
| Aguardando aprovação | `AwaitingApproval`, `awaiting_approval` | Estado em que o orçamento está disponível, reparos adicionais ainda podem ser incluídos e o Cliente pode aprovar. | Em execução |
| Em execução | `InExecution`, `in_execution` | Estado após Aprovação e baixa de estoque bem-sucedidas. | Finalizada |
| Finalizada | `Finalized`, `finalized` | Estado da execução concluída, anterior à Entrega. | Entregue |
| Entregue | `Delivered`, `delivered` | Estado terminal do fluxo concluído. Torna a OS elegível às métricas quando todos os instantes existem. | Finalizada |
| Cancelada | `Cancelled`, `cancelled` | Estado terminal alternativo, permitido somente antes da execução. Não participa das métricas de duração. | Excluída, entregue |
| Token de acompanhamento | `trackingToken`, `tracking_token` | Segredo aleatório de 256 bits específico da OS, devolvido em texto puro somente na criação. O banco armazena apenas seu hash SHA-256. | CPF, JWT administrativo |
| Acompanhamento da OS | `TrackServiceOrder`, `track` | Consulta pública por POST que exige Documento normalizado e Token de acompanhamento correspondentes. Expõe somente status, orçamento e composição apropriada ao Cliente. | Listagem administrativa, autenticação JWT |
| Administrador | `User`, `AdminAuthController` | Usuário interno autenticado por JWT que acessa os cadastros e as ações administrativas da OS. | Cliente |
| Tempo de execução | `ServiceOrderExecutionTimeCalculator` | Duração medida em segundos para OS entregues e completas. Inclui o ciclo total e os intervalos entre estados consecutivos. | Tempo apenas em Em execução |
| Ordem elegível | `eligible_orders` | OS Entregue que possui todos os instantes do fluxo principal. Ordens incompletas ou canceladas são excluídas das médias. | Toda OS criada |

## Regras de linguagem

- Use `Ordem de Serviço` ou `OS` para o agregado; não chame o agregado de `serviço`.
- Use `Serviço` para o item do catálogo e `Serviço da Ordem de Serviço` para sua ocorrência histórica na composição.
- Use `Peça` e `Insumo` como classificações de `Item de estoque`, não como catálogos independentes.
- Use `Orçamento` para a composição de valores e `Aprovação` para a autorização do Cliente; orçamento não é pagamento.
- Use `Finalização` para o término do reparo e `Entrega` para a devolução posterior do veículo.
- Use os nomes dos estados exatamente como definidos neste documento e no enum `ServiceOrderStatus`.
