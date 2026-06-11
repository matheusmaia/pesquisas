# Pesquisas — subdominio por tenant (EasyPanel)

## URLs

| Cliente | URL publica |
|---------|-------------|
| Plansul | `https://plansul.rhtechsantacatarina.com.br/pesquisas` |
| Dressler | `https://dressler.rhtechsantacatarina.com.br/pesquisas` |

## EasyPanel — servico `pesquisas`

**Dominios:**

| Externo | Interno |
|---------|---------|
| `https://plansul.rhtechsantacatarina.com.br/pesquisas` | `http://platform_pesquisas:80/` |
| `https://dressler.rhtechsantacatarina.com.br/pesquisas` | `http://platform_pesquisas:80/` |

(Ajuste `platform_pesquisas` ao nome do servico no seu painel.)

**Variaveis de ambiente:**

```env
PESQUISAS_BASE_PATH=/pesquisas
PESQUISAS_ROUTING_MODE=auto
PESQUISAS_TENANT_DOMAIN_SUFFIX=rhtechsantacatarina.com.br
DB_HOST=mysql
DB_NAME=rhsantacatarina
DB_USER=...
DB_PASS=...
```

**SSL:** ative Let's Encrypt em cada subdominio (igual ao canal).

## Local (XAMPP)

```text
http://localhost/pesquisas/plansul
http://localhost/pesquisas/plansul/responder
```

## Pre-requisito

Questionario com status `publicada` no GESTH para o tenant (seed Plansul).

## Convites (v1.1.12+)

- Link individual: `/pesquisas/responder?token={uuid}` (tenant pelo subdominio).
- Respostas gravam `convite_id` em `pesquisa_respostas_v2` / sessao v2.
- GESTH v1.1.12+: gerar convites e enviar e-mail em **Pesquisas → Convites**.

Deploy GESTH + este repo juntos apos rodar SQLs em `app/database/pesquisa-*` e `permissao-pesquisas-convites-mysql.sql` (ver `DEPLOY.md` secao 5 no repo GESTH).
