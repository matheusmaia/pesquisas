<?php

declare(strict_types=1);

final class View
{
    public static function render(string $template, array $viewData = []): void
    {
        $viewPath = __DIR__ . '/../views/' . $template . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo 'Template nao encontrado.';
            return;
        }

        extract($viewData, EXTR_SKIP);
        require __DIR__ . '/../views/layout.php';
    }
}
