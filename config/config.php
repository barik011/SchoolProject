<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

const DB_HOST = '127.0.0.1';
const DB_NAME = 'school_cms';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: DB_HOST;
    $name = getenv('DB_NAME') ?: DB_NAME;
    $user = getenv('DB_USER') ?: DB_USER;
    $pass = getenv('DB_PASS') ?: DB_PASS;
    $charset = getenv('DB_CHARSET') ?: DB_CHARSET;

    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    return $pdo;
}

function app_path(string $path = ''): string
{
    $base = dirname(__DIR__);
    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
}

function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $documentRoot = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));

    if ($documentRoot !== '' && strpos($projectRoot, $documentRoot) === 0) {
        $relative = trim(substr($projectRoot, strlen($documentRoot)), '/');
        $base = $relative === '' ? '' : '/' . $relative;
    } else {
        $base = '';
    }

    return $base;
}

function url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    $base = base_path();

    if ($cleanPath === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $cleanPath;
}

