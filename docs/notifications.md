# Notificacoes de status

## Escopo implementado

O nucleo de notificacoes do Dia 6 pertence a camada de Aplicacao e nao depende de SMTP, Mailpit, Twilio, Laravel Mail ou transporte HTTP. Ele recebe a identificacao da Ordem de Servico, o novo status ja persistido e os contatos opcionais do Cliente.

O dispatcher aplica a seguinte politica:

| Contatos disponiveis | Tentativas |
| --- | --- |
| nenhum | nenhuma |
| somente e-mail | e-mail |
| somente telefone | SMS |
| e-mail e telefone | e-mail e SMS |

Nao existe preferencia de canal no cadastro nem campo `notification_channel`. O enum interno `NotificationMedium` identifica apenas o transporte que falhou para fins tecnicos de registro; ele nao representa uma escolha do Cliente e nao e persistido.

## Melhor esforco e falhas

Cada canal e tentado de forma independente. Uma excecao no envio de e-mail e registrada e nao impede a tentativa de SMS. Uma excecao no SMS tambem e registrada sem escapar do dispatcher. A falha do proprio logger nao interrompe a tentativa do canal seguinte.

O registro de falha contem somente:

- meio de entrega;
- identificacao da OS;
- novo status;
- classe da excecao.

Destinatario, corpo da mensagem e mensagem retornada pelo fornecedor nao sao registrados. A variavel `NOTIFICATION_FAILURE_LOG_CHANNEL` seleciona um canal de log Laravel existente e usa `stack` por padrao.

## Mensagem minima

A mensagem informa somente a identificacao da OS e seu novo status. Os sete estados suportados sao `received`, `in_diagnosis`, `awaiting_approval`, `in_execution`, `finalized`, `delivered` e `cancelled`.

## Ordem da operacao

O dispatcher nao altera nem persiste a Ordem de Servico. O contrato exige que ele seja chamado somente depois de a transicao ter sido persistida com sucesso. A integracao com todos os casos de uso de transicao e os testes de ausencia de envio em transacao invalida ou revertida pertencem ao Dia 9.

Os adapters concretos tambem permanecem separados:

- Dia 7: e-mail local por Mailpit e producao configuravel por SMTP;
- Dia 8: SMS local fake/log e producao configuravel por Twilio;
- Dia 9: ligacao do dispatcher a todas as transicoes persistidas.

Os testes do Dia 6 usam adapters fake em memoria e nao acessam rede ou servicos pagos.
