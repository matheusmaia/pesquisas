USE rhsantacatarina;

UPDATE empresas
SET dominio_publico_opcional = 'dressler.rhtechsantacatarina.com.br/pesquisas',
    updated_at = NOW()
WHERE slug = 'dressler';

UPDATE empresas
SET dominio_publico_opcional = 'plansul.rhtechsantacatarina.com.br/pesquisas',
    updated_at = NOW()
WHERE slug = 'plansul';
