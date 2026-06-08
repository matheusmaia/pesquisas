<?php

declare(strict_types=1);

final class Config
{
    public const APP_NAME = 'Pesquisa Psicossocial';
    public const COMPANY_SLUG = 'plansul';
    public const BASE_PATH = '/pesquisas';
    public const TENANT_DOMAIN_SUFFIX = 'rhtechsantacatarina.com.br';
    public const ROUTING_MODE = 'auto';

    public const DB_HOST = '127.0.0.1';
    public const DB_PORT = 3306;
    public const DB_NAME = 'rhsantacatarina';
    public const DB_USER = 'root';
    public const DB_PASS = '';

    public static function basePath(): string
    {
        $envValue = getenv('PESQUISAS_BASE_PATH');
        $basePath = is_string($envValue) ? trim($envValue) : '';
        if ($basePath === '') {
            $basePath = self::BASE_PATH;
        }

        $basePath = '/' . trim($basePath, '/');
        return $basePath === '/' ? '' : $basePath;
    }

    public static function dbHost(): string
    {
        $value = getenv('DB_HOST');
        return is_string($value) && trim($value) !== '' ? trim($value) : self::DB_HOST;
    }

    public static function dbPort(): int
    {
        $value = getenv('DB_PORT');
        return is_string($value) && ctype_digit(trim($value)) ? (int) trim($value) : self::DB_PORT;
    }

    public static function dbName(): string
    {
        $value = getenv('DB_NAME');
        return is_string($value) && trim($value) !== '' ? trim($value) : self::DB_NAME;
    }

    public static function dbUser(): string
    {
        $value = getenv('DB_USER');
        return is_string($value) && trim($value) !== '' ? trim($value) : self::DB_USER;
    }

    public static function dbPass(): string
    {
        $value = getenv('DB_PASS');
        return is_string($value) ? $value : self::DB_PASS;
    }

    public static function tenantDomainSuffix(): string
    {
        $value = getenv('PESQUISAS_TENANT_DOMAIN_SUFFIX');
        return is_string($value) && trim($value) !== '' ? trim($value) : self::TENANT_DOMAIN_SUFFIX;
    }

    /** @return 'auto'|'subdomain'|'path' */
    public static function routingMode(): string
    {
        $value = strtolower(trim((string) (getenv('PESQUISAS_ROUTING_MODE') ?: self::ROUTING_MODE)));
        if (!in_array($value, ['auto', 'subdomain', 'path'], true)) {
            return 'auto';
        }

        return $value;
    }
}
