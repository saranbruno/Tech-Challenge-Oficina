# Roteiro de demonstracao

Duracao total estimada: 14 minutos e 30 segundos.

## 1. Apresentacao do projeto - 1m00s

- Nome do projeto: Tech-Challenge-Oficina.
- Contexto da oficina mecanica.
- Objetivo do MVP do backend.

## 2. Arquitetura e documentacao - 1m30s

- Explicar o monolito em DDD pragmatico.
- Mostrar a separacao entre Dominio, Aplicacao, Infraestrutura e Interface HTTP.
- Abrir `docs/architecture.md` e `docs/ddd/diagrams.md`.

## 3. Inicializacao com Docker - 1m30s

- Mostrar `docker compose up -d`.
- Mostrar `docker compose ps`.
- Abrir a aplicacao e o Swagger.

## 4. Autenticacao JWT - 1m30s

- Fazer login administrativo.
- Mostrar resposta com token.
- Acessar uma rota protegida.

## 5. CRUDs administrativos - 3m00s

- Clientes.
- Veiculos.
- Servicos.
- Pecas e insumos.

## 6. Ordem de Servico - 3m00s

- Criar OS.
- Mostrar calculo automatico do orcamento.
- Mostrar diagnostico, disponibilizacao do orcamento e aprovacao.
- Mostrar finalizacao e entrega.

## 7. Cliente e estoque - 1m30s

- Mostrar acompanhamento seguro pelo cliente.
- Mostrar controle de estoque e rejeicao de saldo insuficiente.

## 8. Tempo medio, testes e cobertura - 1m30s

- Mostrar consulta de tempo medio.
- Mostrar suites de testes.
- Mostrar cobertura real.

## 9. Encerramento - 0m30s

- Reforcar que a documentacao e os relatorios estao no repositorio.

