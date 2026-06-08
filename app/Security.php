<?php

declare(strict_types=1);

final class Security
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        $known = $_SESSION['_csrf_token'] ?? '';
        return is_string($token) && is_string($known) && hash_equals($known, $token);
    }

    public static function sessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
