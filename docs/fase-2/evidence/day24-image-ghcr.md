# Dia 24 — Imagem pública e rastreável

Estado: concluído em 2026-10-10, após autorização para o fechamento separado dos Dias 24 e 25.

## Evidências

- A [CD 38096058858](https://github.com/saranbruno/Tech-Challenge-Oficina/actions/runs/38096058858) publicou e implantou o commit `df59d7bf22e60498f7638a50589f5902b620307c`. O deploy passou por migrations, rollouts e quatro smoke tests HTTP 200; destruiu os 15 recursos temporários.
- O pull Docker da imagem desse SHA terminou com sucesso usando um diretório de configuração novo, sem arquivo `config.json` ou credenciais de registry.
- Digest recebido: `sha256:d7e72dc30c5967b7008f5b2832b9b8b59915e5c7a81c9e43f622a63193756982`.
- Labels OCI `revision` e `version`: `df59d7bf22e60498f7638a50589f5902b620307c`; `source`: `https://github.com/saranbruno/Tech-Challenge-Oficina`.
- `.github/workflows/publish-image.yml` publica tags SHA completo e `fase-2`, com permissões `contents: read` e `packages: write`. A CD implanta o SHA; a tag móvel não é referência de rollback.
- Deploy, identificação da imagem e consumo público também foram comprovados no [Dia 26](day26-cd-kind.md). Os overlays recebem a tag por transformação Kustomize em uma cópia temporária.

## Comandos desta conferência

```bash
docker --config .local/evidence/day24/anonymous-docker pull ghcr.io/saranbruno/tech-challenge-oficina:df59d7bf22e60498f7638a50589f5902b620307c
docker inspect ghcr.io/saranbruno/tech-challenge-oficina:df59d7bf22e60498f7638a50589f5902b620307c --format '{{json .RepoDigests}} {{json .Config.Labels}}'
```

Saídas preservadas em `.local/evidence/day24/anonymous-pull.log` e `image-inspect.log`. O healthcheck foi verificado no cluster temporário do runner, sem criar um segundo cluster local. Não foram criados endpoints ou migrations. Requisito F2-IMG-01 atendido; nenhuma pendência do Dia 24.
