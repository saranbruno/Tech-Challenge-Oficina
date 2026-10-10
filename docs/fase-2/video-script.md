# Roteiro de demonstração — Fase 2 — Grupo 55

Autor: Bruno da Silva Saran. Duração alvo: 13:30; limite obrigatório: 15:00.

Estado: roteiro preparado; gravação e publicação ainda pendentes. O roteiro descreve ações a executar, não comprova que o vídeo foi gravado.

## Preparação

- Usar ambiente temporário com dados fictícios e Secrets privados; não exibir `.env`, kubeconfig, tfstate, JWT, tracking tokens ou assinaturas HMAC.
- Verificar áudio, legibilidade de terminal e navegador e remover notificações da tela.
- Disparar a CD manualmente antes da gravação para disponibilizar as etapas reais. Mostrar a execução concluída e a origem real. Uma reexecução manual conserva o evento original `push`; identifique a tentativa reexecutada e não a apresente como `workflow_dispatch`.
- Preparar Swagger, Actions, arquitetura, pods, HPA e Mailpit em abas separadas. Executar carga em uma sessão e observar HPA em outra.
- Começar a carga antes do trecho de escalabilidade, preservando o início e a mudança real das réplicas na gravação. Não reduzir o tempo do vídeo com métricas inventadas.
- Usar o roteiro de API de `scripts/validate-delivery.py` somente em ambiente descartável. Ele cria cadastros e OS fictícios; as credenciais são lidas do ambiente privado, sem serem impressas.

## Sequência

| Tempo | Demonstração | Evidência visível |
| --- | --- | --- |
| 00:00–00:40 | Apresentar projeto, autor, grupo e objetivo da Fase 2. | Repositório público e branch da demonstração. |
| 00:40–01:40 | Explicar dependências internas e adapters. | Diagrama da Clean Architecture e diretórios reais. |
| 01:40–03:10 | Mostrar provisionamento e deploy. | Terraform Kind/PostgreSQL/Metrics Server, migrations Complete, API/Mailpit Ready e PVC Bound. |
| 03:10–04:40 | Mostrar CI/CD completo. | Execução manual real; testes/cobertura, GHCR SHA, deployment e limpeza `always`. |
| 04:40–06:10 | Cadastrar cliente, veículo, serviço e item; abrir OS. | HTTP 201, ID explícito e orçamento calculado pelo servidor. |
| 06:10–07:10 | Consultar status como administrador e cliente. | JWT administrativo; resposta mínima do cliente; combinação inválida sem exposição de dados. |
| 07:10–09:00 | Diagnosticar e enviar decisões externas. | HMAC inválido rejeitado; approved → in_execution; repetição sem baixa dupla; rejected → cancelled sem baixa. |
| 09:00–10:20 | Mostrar notificações e fila operacional. | Mailpit, SMS simulado sem dados pessoais; clientes sem contatos, com um e com dois; ordenação e exclusão dos terminais. |
| 10:20–12:20 | Mostrar carga e escalabilidade. | CPU e memória conhecidas; HPA 1 → múltiplas réplicas; pods Ready e resultados HTTP reais. Mostrar retorno ao mínimo se houver tempo ou evidência previamente capturada. |
| 12:20–13:10 | Mostrar persistência e critérios de segurança. | PVC mantido após recriação do PostgreSQL; secrets externos; auditoria e limites do scan. |
| 13:10–13:30 | Encerrar com links e rastreabilidade. | Repositório, PDF com arquitetura e link definitivo do vídeo. |

## Conferência após a gravação

1. Conferir duração real de até 15:00 e reprodução com áudio legível.
2. Conferir presença de deploy, CI/CD, consumo de APIs e scale-up automático visíveis.
3. Revisar quadros para garantir que nenhum segredo ou dado pessoal real foi exposto.
4. Publicar no YouTube ou Vimeo como público ou não listado e testar acesso sem autenticação.
5. Inserir o endereço definitivo no README e em `docs/fase-2/final-delivery.html`, regenerar o PDF e testar seus links.
6. Registrar URL, duração e resultados em `docs/fase-2/evidence/day30-final-audit.md`.

A publicação do vídeo não é comprovada por este roteiro. O fechamento da Fase 2 exige também acesso do avaliador, matriz completa, CI verde e reprodução da main após integração autorizada.
