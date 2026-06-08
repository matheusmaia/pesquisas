<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = rtrim(Config::basePath(), '/');

    if ($base !== '' && str_starts_with((string) $path, $base)) {
        $path = substr((string) $path, strlen($base));
    }

    $path = '/' . ltrim((string) $path, '/');

    return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
}

function resolve_tenant_slug_from_host(): ?string
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host) ?? $host;
    if ($host === '' || $host === 'localhost') {
        return null;
    }

    $suffix = strtolower(Config::tenantDomainSuffix());
    $reserved = ['www', 'gesth', 'admissao', 'admissão', 'localhost'];

    if ($suffix !== '' && str_ends_with($host, '.' . $suffix)) {
        $sub = substr($host, 0, -strlen('.' . $suffix));
        if ($sub !== '' && !in_array($sub, $reserved, true) && preg_match('/^[a-z0-9-]{2,40}$/', $sub)) {
            return $sub;
        }
    }

    return null;
}

function uses_subdomain_routing(): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $mode = Config::routingMode();
    if ($mode === 'subdomain') {
        return $cached = true;
    }
    if ($mode === 'path') {
        return $cached = false;
    }

    return $cached = resolve_tenant_slug_from_host() !== null;
}

function tenant_public_domain(string $slug): string
{
    $slug = strtolower(trim($slug));
    $base = Config::basePath();

    return $slug . '.' . Config::tenantDomainSuffix() . ($base !== '' ? $base : '');
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function redirect(string $path): void
{
    header('Location: ' . Config::basePath() . $path);
    exit;
}

function set_tenant_context(array $tenant): void
{
    $_SESSION['_tenant'] = $tenant;
}

function tenant_context(): array
{
    $tenant = $_SESSION['_tenant'] ?? null;
    if (!is_array($tenant) || empty($tenant['slug'])) {
        return [
            'slug' => Config::COMPANY_SLUG,
            'nome' => 'Plansul',
        ];
    }

    return $tenant;
}

function tenant_slug(): string
{
    return (string) (tenant_context()['slug'] ?? Config::COMPANY_SLUG);
}

function tenant_path(string $suffix = ''): string
{
    $suffix = '/' . ltrim($suffix, '/');
    if ($suffix === '/') {
        $suffix = '';
    }

    if (uses_subdomain_routing()) {
        return $suffix === '' ? '/' : $suffix;
    }

    return '/' . tenant_slug() . $suffix;
}

function tenant_url(string $suffix = ''): string
{
    return Config::basePath() . tenant_path($suffix);
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function with_old_input(array $input): void
{
    $_SESSION['_old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $stored = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $stored;
}

function back_with_errors(array $errors, string $path): void
{
    $_SESSION['_errors'] = $errors;
    redirect($path);
}

function errors(): array
{
    $errors = $_SESSION['_errors'] ?? [];
    unset($_SESSION['_errors']);
    return $errors;
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $time = strtotime($date);
    return $time ? date('d/m/Y H:i', $time) : $date;
}

function normalize_hex_color(string $value, string $fallback): string
{
    $value = strtoupper(trim($value));
    if (!preg_match('/^#[0-9A-F]{6}$/', $value)) {
        return strtoupper($fallback);
    }

    return $value;
}

function hex_to_rgb(string $hex): array
{
    $hex = ltrim(normalize_hex_color($hex, '#000000'), '#');

    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function rgb_to_hex(int $r, int $g, int $b): string
{
    return sprintf('#%02X%02X%02X', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
}

function mix_hex_colors(string $hex1, string $hex2, float $weight2): string
{
    [$r1, $g1, $b1] = hex_to_rgb($hex1);
    [$r2, $g2, $b2] = hex_to_rgb($hex2);
    $weight2 = max(0.0, min(1.0, $weight2));
    $weight1 = 1.0 - $weight2;

    return rgb_to_hex(
        (int) round(($r1 * $weight1) + ($r2 * $weight2)),
        (int) round(($g1 * $weight1) + ($g2 * $weight2)),
        (int) round(($b1 * $weight1) + ($b2 * $weight2))
    );
}

function hex_luminance(string $hex): float
{
    [$r, $g, $b] = hex_to_rgb($hex);

    return ((0.299 * $r) + (0.587 * $g) + (0.114 * $b)) / 255;
}

function contrast_text_color(string $hex): string
{
    return hex_luminance($hex) > 0.58 ? '#0f172a' : '#ffffff';
}

function hex_to_rgb_css(string $hex): string
{
    [$r, $g, $b] = hex_to_rgb($hex);

    return "{$r}, {$g}, {$b}";
}

function tenant_theme_css_vars(string $primary, string $secondary): string
{
    $brand = normalize_hex_color($primary, '#0B2A5A');
    $accent = normalize_hex_color($secondary, '#17A2B8');
    $brand2 = mix_hex_colors($brand, $accent, 0.34);
    $accent2 = mix_hex_colors($accent, '#000000', 0.16);
    $brandRgb = hex_to_rgb_css($brand);
    $accentRgb = hex_to_rgb_css($accent);

    $vars = [
        '--brand' => $brand,
        '--brand2' => $brand2,
        '--accent' => $accent,
        '--accent2' => $accent2,
        '--primary' => $brand,
        '--secondary' => $accent,
        '--brand-rgb' => $brandRgb,
        '--accent-rgb' => $accentRgb,
        '--brand-soft' => "rgba({$brandRgb}, 0.08)",
        '--brand-soft-2' => "rgba({$brandRgb}, 0.12)",
        '--accent-soft' => "rgba({$accentRgb}, 0.14)",
        '--line' => '#e2e8f0',
        '--text' => '#0f172a',
        '--muted' => '#64748b',
        '--primary-contrast' => contrast_text_color($brand),
        '--accent-contrast' => contrast_text_color($accent),
        '--bg' => '#f4f7fb',
        '--card-border' => '#e2e8f0',
        '--focus-ring' => "rgba({$brandRgb}, 0.22)",
        '--btn-shadow' => "rgba({$brandRgb}, 0.22)",
        '--cta-shadow' => "rgba({$accentRgb}, 0.24)",
        '--danger' => '#ef4444',
        '--success' => '#16a34a',
        '--warning-bg' => '#fff4e5',
    ];

    $parts = [];
    foreach ($vars as $key => $value) {
        $parts[] = "{$key}: {$value}";
    }

    return implode('; ', $parts) . ';';
}

function tenant_body_class(): string
{
    $slug = tenant_slug();

    return 'tenant-' . preg_replace('/[^a-z0-9-]/', '', $slug);
}

function tenant_watermark_path(): string
{
    $watermarks = [
        'plansul' => 'img/plansul-mapa-brasil.svg',
        'dressler' => 'img/capa-dressler-2025.png',
    ];

    $slug = tenant_slug();

    return $watermarks[$slug] ?? 'img/capa-dressler-2025.png';
}

function tenant_watermark_url(): string
{
    return tenant_url('/' . ltrim(tenant_watermark_path(), '/'));
}
