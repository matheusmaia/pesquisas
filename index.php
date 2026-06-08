<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$path = request_path();
$method = request_method();
$segments = array_values(array_filter(explode('/', trim($path, '/')), static fn ($part) => $part !== ''));

$repo = new PesquisaRepository(Database::connection());

if (uses_subdomain_routing()) {
    $slug = resolve_tenant_slug_from_host() ?? '';
    $relativePath = $path;
} else {
    $slug = $segments[0] ?? '';
    $relativePath = '/' . implode('/', array_slice($segments, 1));
    if ($relativePath === '/') {
        $relativePath = '/';
    }

    if ($slug === '') {
        redirect('/' . Config::COMPANY_SLUG);
    }
}

if (!preg_match('/^[a-z0-9-]{2,40}$/', $slug)) {
    http_response_code(404);
    View::render('404', ['title' => 'Pagina nao encontrada']);
    exit;
}

$company = $repo->findCompanyBySlug($slug);

if ($company === null) {
    http_response_code(404);
    View::render('404', ['title' => 'Pagina nao encontrada']);
    exit;
}

$tenant = [
    'id' => (int) $company['id'],
    'slug' => (string) $company['slug'],
    'nome' => (string) $company['nome'],
    'logo' => (string) ($company['logo'] ?? ''),
    'cor_primaria' => (string) ($company['cor_primaria'] ?? ''),
    'cor_secundaria' => (string) ($company['cor_secundaria'] ?? ''),
];
set_tenant_context($tenant);

$controller = new PublicController($tenant);

$routes = [
    'GET' => [
        '/' => [$controller, 'home'],
        '/responder' => [$controller, 'respond'],
        '/obrigado' => [$controller, 'thankyou'],
        '/politica' => [$controller, 'policy'],
    ],
    'POST' => [
        '/responder' => [$controller, 'submitRespond'],
    ],
];

$handler = $routes[$method][$relativePath] ?? null;
if ($handler) {
    call_user_func($handler);
    exit;
}

http_response_code(404);
View::render('404', ['title' => 'Pagina nao encontrada']);
