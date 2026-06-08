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
    'Plansul',
    'plansul',
    'plansul.rhtechsantacatarina.com.br/pesquisas',
    'img/logo-plansul.webp',
    '#0B2A5A',
    '#FF6A3E',
    'ativo'
)
ON DUPLICATE KEY UPDATE
    nome = 'Plansul',
    dominio_publico_opcional = 'plansul.rhtechsantacatarina.com.br/pesquisas',
    logo = 'img/logo-plansul.webp',
    cor_primaria = '#0B2A5A',
    cor_secundaria = '#FF6A3E',
    status = 'ativo',
    updated_at = NOW();
