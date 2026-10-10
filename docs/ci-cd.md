# Imagem de container e CI/CD

## Integracao continua

O workflow `.github/workflows/ci.yml` executa em `pull_request` e em push para `fase-2` ou `main`. Ele tem somente a permissao `contents: read` e cancela uma execucao anterior da mesma referencia quando uma nova execucao comeca.

O job usa PostgreSQL 18.4 como servico efemero e instala as dependencias PHP com cache do Composer. Antes dos testes, aplica as migrations no banco de teste. A validacao inclui Pint, as suites de dominio e integracao com PCOV e relatorios Clover, lint do OpenAPI, `composer audit`, `terraform fmt` e `validate` nos ambientes local e CI, renderizacao Kustomize e validacao estrita de esquema dos dois overlays Kubernetes com kubeconform 0.8.0 contra Kubernetes 1.35.0, alem do build Docker sem publicacao. O pacote do kubeconform e conferido por SHA-256 antes da instalacao. Essa validacao nao exige cluster; a aplicacao real dos manifestos e verificada no Kind temporario pela CD. Os relatorios de cobertura sao preservados como artefato mesmo se uma etapa anterior falhar.

A CI gera uma `APP_KEY` aleatoria de 32 bytes no runner antes das migrations, registra seu mascaramento no GitHub Actions e nao grava o valor no repositorio. Na execucao remota do commit `fc65370`, a chave de teste anterior decodificava para 38 bytes e causou tres erros `Unsupported cipher or incorrect key length` na suite HTTP. No commit `06fa53f`, os testes passaram, mas a etapa de manifestos falhou porque `kubectl apply --dry-run=client` tentou consultar a API de um cluster inexistente. No commit `89bccc8`, a CI completa passou, inclusive a validacao dos dois overlays com kubeconform e o build da imagem.

O workflow nao publica imagem e nao usa Secrets de producao. A associacao de regras de protecao de branch para exigir o job `Validar aplicacao e infraestrutura` permanece uma configuracao do repositorio no GitHub.

## Entrega continua no Kind temporario

O workflow `.github/workflows/cd-kind.yml` executa em push para `fase-2` ou manualmente. Primeiro reutiliza a CI; depois reutiliza o workflow de publicacao do GHCR. O deploy usa a tag SHA do commit, nunca a tag movel `fase-2`.

No mesmo runner, o workflow instala Kind, gera uma senha PostgreSQL e Secrets da aplicacao somente para aquela execucao, aplica primeiro o modulo Terraform do cluster para criar o kubeconfig e depois o plano completo do ambiente `ci`, espera o StatefulSet PostgreSQL, renderiza uma copia temporaria do overlay CI, executa o Job de migrations antes do Deployment da API e espera Mailpit e API. Os smoke tests internos verificam `/up`, Swagger, OpenAPI e Mailpit.

Na execucao `38091893737`, o cluster e o PostgreSQL foram criados, mas a renderizacao falhou porque o arquivo temporario de Secrets estava fora do diretorio permitido pelo Kustomize. A limpeza `always` destruiu os 15 recursos Terraform. O arquivo temporario passou a ser criado dentro do overlay, com permissao restrita, e as credenciais geradas via ambiente passaram a ser mascaradas antes de chegar aos logs do runner.

A execucao [38092377887](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38092377887), commit `eafd711`, concluiu CI, publicacao, provisionamento, migrations, rollout, quatro smoke tests HTTP 200, coleta de artefatos e destruicao dos 15 recursos. Um pull Docker com configuracao vazia comprovou o consumo anonimo da imagem SHA publicada; os labels OCI e o Deployment coletado identificam o mesmo commit. Os resultados, a cobertura medida e os limites estao em [evidencias do Dia 26](fase-2/evidence/day26-cd-kind.md).

Antes da limpeza, pods, Deployments, Services, Job, HPA, eventos, logs e outputs Terraform sao publicados como artefato. A etapa `Destruir ambiente temporario` usa `if: always()` no mesmo job do deploy e executa `terraform destroy`, inclusive quando uma etapa anterior falha. Isso nunca toca o ambiente Terraform local persistente.

## Publicacao no GHCR

O workflow reutilizavel `.github/workflows/publish-image.yml` publica a API no pacote publico `ghcr.io/saranbruno/tech-challenge-oficina`. Ele so executa na branch `fase-2`, usa o `GITHUB_TOKEN` com as permissoes minimas `contents: read` e `packages: write` e nao recebe credenciais de registry versionadas.

Cada publicacao cria duas tags para o mesmo build:

- o SHA completo do commit, que e a referencia imutavel usada em deploys;
- `fase-2`, que e uma referencia auxiliar e movel para a ultima imagem publicada pela branch.

Os labels OCI incluem origem, revisao e versao. Revisao e versao usam o SHA completo, permitindo relacionar uma imagem implantada ao commit que a gerou.

Depois de tornar o pacote publico nas configuracoes do GHCR, valide o consumo sem credencial com:

```bash
docker logout ghcr.io
docker pull ghcr.io/saranbruno/tech-challenge-oficina:<sha-completo>
docker inspect ghcr.io/saranbruno/tech-challenge-oficina:<sha-completo> --format '{{ index .Config.Labels "org.opencontainers.image.revision" }}'
```

O pull deve ocorrer antes de considerar a imagem pronta para clusters. A publicacao e essa validacao externa devem ser registradas com o SHA e a saida reais no progresso do Dia 24.

## Uso nos overlays Kubernetes

Os overlays `local` e `ci` definem `fase-2` como tag padrao para a imagem base. Um deploy rastreavel substitui essa tag pelo SHA que acabou de ser publicado antes de renderizar o overlay:

```bash
cd k8s/overlays/ci
kustomize edit set image ghcr.io/saranbruno/tech-challenge-oficina=ghcr.io/saranbruno/tech-challenge-oficina:<sha-completo>
kustomize build .
```

Essa substituicao deve ocorrer em uma copia temporaria do overlay no runner. Os manifestos versionados preservam a tag auxiliar e o commit de deploy permanece identificavel pela tag SHA aplicada.

## Rollback

Para voltar a uma versao conhecida no cluster local, aplique o mesmo procedimento usando o SHA completo previamente validado. Nunca use `fase-2` como referencia de rollback, pois essa tag e movel. O workflow de CD cria e destroi um cluster temporario por execucao; ele nao implementa rollback automatico nem altera o cluster local persistente.
