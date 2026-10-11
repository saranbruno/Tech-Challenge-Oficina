# Proteção de branch — configuração pendente do Dia 25

A CI já verifica o código. A proteção de branch é a configuração que exige o resultado aprovado antes do merge. O enunciado exige CI/CD; a tarefa de impedir merge quando etapas obrigatórias falham foi aprovada no roadmap do Dia 25.

Em 2026-10-10, os metadados públicos mostraram `protected=false` para `main` e `fase-2`, e a lista de rulesets estava vazia. A integração retornou HTTP 403 no endpoint administrativo de proteção. O usuário pediu para concluir as validações disponíveis e fazer essa configuração depois.

## Configurar depois das validações

Abra [Settings → Branches](https://github.com/saranbruno/Tech-Challenge-Oficina/settings/branches) com a conta administradora.

1. Escolha **Add classic branch protection rule**.
2. Em **Branch name pattern**, informe `main`.
3. Marque **Require a pull request before merging**.
4. Marque **Require status checks to pass before merging** e selecione `Validar aplicacao e infraestrutura`, produzido pelo GitHub Actions.
5. Marque **Require branches to be up to date before merging**.
6. Marque **Do not allow bypassing the above settings**, para aplicar a exigência também ao administrador.
7. Salve em **Create** e repita para `fase-2`.

Não é necessário exigir aprovação de outra pessoa para esse trabalho individual. Após ativar as regras, alterações em `fase-2` também precisam entrar por PR com CI verde.

Procedimento conferido na [documentação oficial do GitHub](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/managing-a-branch-protection-rule).

## Evidência para fechar o Dia 25

Conferir as duas regras ativas e o nome exato do check obrigatório. Como a integração não possui acesso administrativo, capturas das regras salvas podem complementar os metadados públicos; `protected=true` sozinho não prova qual check é obrigatório. Não enviar tokens ou credenciais.

O teste controlado de CI está documentado nas [evidências do Dia 25](evidence/day25-ci.md). A proteção só será registrada como concluída após a conferência da configuração real.
