# Artefatos da Fase 2

Os artefatos finais da Fase 1 permanecem em `docs/`. Estes arquivos pertencem somente à Fase 2:

- [Documento HTML](final-delivery.html): arquitetura incorporada, evidências e estado da entrega.
- [Documento PDF](final-delivery.pdf): versão exportada do HTML, com links clicáveis.
- [Roteiro do vídeo](video-script.md): duração alvo 13:30; gravação/publicação ainda pendentes.
- [Reprodução em ambiente limpo do Dia 29](evidence/day29-clean-environment.md).
- [Auditoria do Dia 30](evidence/day30-final-audit.md): rastreabilidade e condições de encerramento.

HTML e PDF estão em preparação. Não representam entrega encerrada enquanto houver condições obrigatórias pendentes. Quando existir vídeo público ou não listado, inserir o endereço no HTML e no README principal, regenerar o PDF e conferir os links e a duração real.

## Regenerar o PDF

Na raiz do repositório, com Docker:

```bash
docker run --rm --user "$(id -u):$(id -g)" \
  -v "$PWD/docs/fase-2:/data" \
  --entrypoint node ghcr.io/mermaid-js/mermaid-cli/mermaid-cli:11.12.0 \
  -e 'const puppeteer = require("/home/mermaidcli/node_modules/puppeteer"); (async () => { const browser = await puppeteer.launch({executablePath:"/usr/bin/chromium-browser",args:["--no-sandbox","--disable-dev-shm-usage"],timeout:120000}); try { const page = await browser.newPage(); await page.goto("file:///data/final-delivery.html",{waitUntil:"networkidle0",timeout:120000}); await page.pdf({path:"/data/final-delivery.pdf",format:"A4",printBackground:true,preferCSSPageSize:true}); } finally { await browser.close(); } })().catch(error => { console.error(error.message); process.exit(1); });'
```

A geração usa a mesma imagem do Mermaid CLI empregada para revisar os diagramas, sem instalar dependências na aplicação. O HTML contém os três SVG, sem depender dos arquivos ignorados de evidência. Abra o PDF e confira arquitetura, paginação e links após cada alteração.

## Validar APIs e notificações em Compose descartável

Configure `.env` privado, inicie os serviços, aplique migrations e seed conforme o README principal. Depois:

```bash
python3 scripts/validate-delivery.py \
  --env-file .env --compose-dir . \
  --url http://127.0.0.1:8081 --mailpit-url http://127.0.0.1:8025 \
  --output .local/evidence/delivery/api-validation.json \
  --allow-fixtures
```

A validação exige Python 3 e Docker Compose. Ela cria quatro clientes fictícios, veículos, serviços, itens e OS para verificar contatos ausentes, apenas e-mail, apenas telefone e ambos. Consome os endpoints reais, confere HMAC ausente/inválido/válido, decisões repetidas, estoque, consulta mínima e exclusão dos terminais da fila. Confere mensagens no Mailpit e SMS simulados no log, sem imprimir documentos ou credenciais.

Use somente um banco descartável: os dados criados permanecem nele até a limpeza desse ambiente. Portas, caminho do `.env` e diretório Compose devem apontar ao mesmo ambiente isolado. A suíte PHPUnit continua responsável pelos testes de falha independente dos canais e demais regressões.
