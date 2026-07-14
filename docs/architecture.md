# Arquitetura do projeto

## Abordagem

O Tech-Challenge-Oficina utiliza um monolito Laravel organizado por DDD pragmatico e arquitetura em camadas. A separacao existe para manter as regras de negocio independentes do transporte HTTP e permitir que futuros controllers, inclusive controllers Inertia, reutilizem os mesmos casos de uso.

## Camadas

### Dominio

Local previsto: `app/Domain`.

Contem entidades, objetos de valor, enums, regras de transicao, calculos e excecoes de negocio. Nao depende de Laravel, Eloquent, HTTP ou formatos de resposta.

### Aplicacao

Local previsto: `app/Application`.

Contem casos de uso, comandos e objetos de entrada e saida. Coordena dominio e persistencia por contratos definidos na fronteira da aplicacao. Nao conhece Request, JsonResponse ou Inertia.

### Infraestrutura

Local previsto: `app/Infrastructure`.

Contem implementacoes tecnicas, incluindo persistencia Eloquent, PostgreSQL e JWT. Implementa os contratos exigidos pelas camadas internas e pode depender do framework.

### Interface HTTP

Local: `app/Interfaces/Http`.

Contem controllers, Form Requests, API Resources e respostas HTTP. Converte a entrada HTTP para os casos de uso e transforma suas saidas em respostas da API. Controllers devem permanecer pequenos e sem regras de negocio.

## Direcao das dependencias

As dependencias seguem o sentido `Interface HTTP -> Aplicacao -> Dominio`. A Infraestrutura fornece implementacoes para as fronteiras internas e e conectada pelo container do Laravel. O Dominio nao depende das demais camadas.

Eloquent permanece na Infraestrutura. Form Requests e API Resources permanecem na Interface HTTP. Casos de uso nao retornam respostas JSON e podem ser chamados por qualquer adaptador de interface.

## Rotas

As rotas da API possuem o prefixo global `/api`.

- `routes/api/admin.php`: futuras rotas administrativas protegidas por JWT.
- `routes/api/client.php`: futuras rotas de acompanhamento protegidas pelo mecanismo do cliente.

Os arquivos estao carregados e agrupados sob `/api/admin` e `/api/client`, mas permanecem sem endpoints ate que as funcionalidades correspondentes sejam implementadas.

## Erros da API

Erros da API utilizam um envelope consistente:

```json
{
    "error": {
        "code": "not_found",
        "message": "Not Found",
        "details": {}
    }
}
```

`details` e omitido quando nao ha informacoes adicionais. Erros de validacao usam `validation_error`, falhas de autenticacao usam `unauthenticated` e respostas HTTP conhecidas possuem codigos estaveis.

## Convencoes

- Identificadores do codigo e contratos da API sao escritos em ingles.
- Documentacao de entrega e mensagens destinadas ao usuario sao escritas em portugues do Brasil.
- Valores monetarios usam representacao exata e nunca `float`.
- Regras de negocio sao testadas prioritariamente como testes unitarios de Dominio.
- Fluxos HTTP e persistencia PostgreSQL sao cobertos por testes de integracao.
- Novas classes e diretorios sao criados somente quando houver comportamento concreto.
