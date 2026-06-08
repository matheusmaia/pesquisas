<?php

declare(strict_types=1);

$cssFile = dirname(__DIR__) . '/public/assets/css/style.css';
$cssVersion = is_file($cssFile) ? (string) filemtime($cssFile) : '1';
$tenant = tenant_context();
$tenantName = (string) ($tenant['nome'] ?? 'Cliente');
$tenantPrimary = (string) ($tenant['cor_primaria'] ?? '');
$tenantSecondary = (string) ($tenant['cor_secundaria'] ?? '');

$isValidHexColor = static function (string $value): bool {
    return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
};

if (!$isValidHexColor($tenantPrimary)) {
    $tenantPrimary = '#0B2A5A';
}
if (!$isValidHexColor($tenantSecondary)) {
    $tenantSecondary = '#FF6A3E';
}

$tenantLogo = trim((string) ($tenant['logo'] ?? ''));
$tenantLogoUrl = '';
if ($tenantLogo !== '') {
    if (preg_match('/^https?:\/\//i', $tenantLogo)) {
        $tenantLogoUrl = $tenantLogo;
    } else {
        $tenantLogoUrl = tenant_url('/' . ltrim($tenantLogo, '/'));
    }
}
if ($tenantLogoUrl === '') {
    $tenantLogoUrl = tenant_url('/img/logo-plansul.webp');
}

$faviconUrl = $tenantLogoUrl;
$themeVars = tenant_theme_css_vars($tenantPrimary, $tenantSecondary);
$bodyClass = tenant_body_class();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Pesquisa Psicossocial') . ' | ' . $tenantName) ?></title>
    <meta name="description" content="<?= e('Pesquisa psicossocial de ' . $tenantName . '. Respostas anônimas e confidenciais.') ?>">
    <link rel="icon" type="image/png" href="<?= e($faviconUrl) ?>">
    <link rel="stylesheet" href="<?= e(tenant_url('/public/assets/css/style.css')) ?>?v=<?= e($cssVersion) ?>">
</head>
<body class="<?= e($bodyClass) ?>" style="<?= e($themeVars) ?>">
    <header class="topbar">
        <div class="container topbar-content">
            <a class="brand" href="<?= e(tenant_url()) ?>" aria-label="Pesquisa Psicossocial">
                <img src="<?= e($tenantLogoUrl) ?>" alt="<?= e($tenantName) ?>">
            </a>
            <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-controls="main-menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="menu" id="main-menu">
                <a href="<?= e(tenant_url()) ?>">Início</a>
                <a href="<?= e(tenant_url('/responder')) ?>">Responder</a>
                <a href="<?= e(tenant_url('/politica')) ?>">Privacidade</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <div class="container">
            <?php require $viewPath; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <span class="footer-version">Versão do portal: 1.0.0</span>
            <span class="footer-empresa"><?= e('Pesquisa oficial ' . $tenantName) ?></span>
            <div class="footer-brand">
                <img src="<?= e(tenant_url('/img/logo-gesth.png')) ?>" alt="GESTH" class="footer-brand-icon" width="28" height="28">
                <div class="footer-brand-text">
                    <strong>GESTH | Plataforma de RH e Gestão</strong><br>
                    <span class="footer-powered">Powered by RH Tech Santa Catarina</span>
                </div>
            </div>
        </div>
    </footer>
    <script>
        (() => {
            const menu = document.getElementById('main-menu');
            const toggle = document.querySelector('.menu-toggle');
            if (!menu || !toggle) return;

            toggle.addEventListener('click', () => {
                const isOpen = menu.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            menu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    menu.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });
        })();
    </script>
</body>
</html>
