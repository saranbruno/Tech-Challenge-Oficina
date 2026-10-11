# Proteção de branch — conferência do Dia 25

A CI já verifica o código. A proteção de branch é a configuração que exige o resultado aprovado antes do merge. O enunciado exige CI/CD; a tarefa de impedir merge quando etapas obrigatórias falham foi aprovada no roadmap do Dia 25.

Em 2026-10-10, os metadados públicos mostraram `protected=false` para `main` e `fase-2`, e a lista de rulesets estava vazia. A integração retornou HTTP 403 no endpoint administrativo de proteção. O usuário pediu para concluir as validações disponíveis e fazer essa configuração depois.

## Configuração de referência

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

## Evidência conferida em 2026-10-10

O usuário configurou as regras. Os endpoints de metadados `branches/main` e `branches/fase-2` retornaram `protected=true`, com `required_status_checks.contexts` contendo `Validar aplicacao e infraestrutura`, `app_id=15368` (GitHub Actions) e `enforcement_level=everyone`.

A leitura administrativa completa ainda retorna HTTP 403; a exigência do check e a aplicação a todos são visíveis nos metadados das branches. As demais opções da interface não foram verificadas individualmente e não são usadas como evidência. Os resultados da CI verde e da falha controlada estão nas [evidências do Dia 25](evidence/day25-ci.md).

Alterações em `fase-2` seguem por PR com o check aprovado. A integração final em `main` continua pertencendo ao Dia 30, com vídeo e autorização pendentes.
