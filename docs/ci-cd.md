# Imagem de container e CI/CD

## Integracao continua

O workflow `.github/workflows/ci.yml` executa em `pull_request` e em push para `fase-2` ou `main`. Ele tem somente a permissao `contents: read` e cancela uma execucao anterior da mesma referencia quando uma nova execucao comeca.

O job usa PostgreSQL 18.4 como servico efemero e instala as dependencias PHP com cache do Composer. Antes dos testes, aplica as migrations no banco de teste. A validacao inclui Pint, as suites de dominio e integracao com PCOV e relatorios Clover, lint do OpenAPI, `composer audit`, `terraform fmt` e `validate` nos ambientes local e CI, renderizacao e dry-run client dos dois overlays Kubernetes e build Docker sem publicacao. Os relatorios de cobertura sao preservados como artefato mesmo se uma etapa anterior falhar.

O workflow nao publica imagem e nao usa Secrets de producao. A associacao de regras de protecao de branch para exigir o job `Validar aplicacao e infraestrutura` permanece uma configuracao do repositorio no GitHub.

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

Para voltar a uma versao conhecida, aplique o mesmo procedimento usando o SHA completo previamente validado. Nunca use `fase-2` como referencia de rollback, pois essa tag e movel. O rollback do deploy sera automatizado no fluxo de entrega continua do Dia 26.
