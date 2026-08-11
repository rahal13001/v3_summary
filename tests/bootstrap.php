<?php

require dirname(__DIR__).'/vendor/autoload.php';

$environment = static fn (string $key): mixed => $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

if ($environment('DB_CONNECTION') !== 'sqlite' || $environment('DB_DATABASE') !== ':memory:') {
    throw new RuntimeException('Automated tests must use the isolated SQLite in-memory database.');
}

$configCache = $environment('APP_CONFIG_CACHE');

if (! is_string($configCache) || $configCache === '') {
    throw new RuntimeException('Automated tests must bypass the application configuration cache.');
}

$configCachePath = dirname(__DIR__).DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configCache);

if (is_file($configCachePath)) {
    throw new RuntimeException('The PHPUnit-specific configuration cache must not exist.');
}

$testStoragePath = sys_get_temp_dir()
    .DIRECTORY_SEPARATOR
    .'summary-laravel-tests-'
    .substr(hash('sha256', dirname(__DIR__)), 0, 12);
$compiledViewsPath = $testStoragePath.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views';

if (! is_dir($compiledViewsPath) && ! mkdir($compiledViewsPath, 0777, true) && ! is_dir($compiledViewsPath)) {
    throw new RuntimeException("Unable to create the isolated test storage path [{$compiledViewsPath}].");
}

$_ENV['LARAVEL_STORAGE_PATH'] = $testStoragePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $testStoragePath;
