# Portal de Pesquisas Psicossociais (multi-tenant)

## URLs

### Producao

- `https://plansul.rhtechsantacatarina.com.br/pesquisas`
- `https://dressler.rhtechsantacatarina.com.br/pesquisas`

### Local

- `http://localhost/pesquisas/plansul`

## Rotas

- `/` — pagina inicial
- `/responder` — formulario da pesquisa publicada
- `/obrigado` — confirmacao
- `/politica` — politica de privacidade

## Variaveis de ambiente

| Variavel | Padrao | Descricao |
|----------|--------|-----------|
| `PESQUISAS_BASE_PATH` | `/pesquisas` | Prefixo do app |
| `PESQUISAS_ROUTING_MODE` | `auto` | `auto`, `subdomain` ou `path` |
| `PESQUISAS_TENANT_DOMAIN_SUFFIX` | `rhtechsantacatarina.com.br` | Sufixo DNS |
| `DB_*` | — | Conexao MySQL (mesmo banco GESTH) |

## Deploy

Ver `docs/easypanel-subdominio-pesquisas.md` e `Dockerfile`.
