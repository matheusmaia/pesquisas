USE rhsantacatarina;

INSERT INTO empresas (
    nome,
    slug,
    dominio_publico_opcional,
    logo,
    cor_primaria,
    cor_secundaria,
    status
) VALUES (
    'Dressler BPO',
    'dressler',
    'dressler.rhtechsantacatarina.com.br/pesquisas',
    'img/logo-dressler-bpo.png',
    '#A7C944',
    '#63B5B7',
    'ativo'
)
ON DUPLICATE KEY UPDATE
    nome = 'Dressler BPO',
    dominio_publico_opcional = 'dressler.rhtechsantacatarina.com.br/pesquisas',
    logo = 'img/logo-dressler-bpo.png',
    cor_primaria = '#A7C944',
    cor_secundaria = '#63B5B7',
    status = 'ativo',
    updated_at = NOW();
