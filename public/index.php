<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

function frontControllerEnvValue($key, $default = null)
{
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) {
        return $default;
    }

    $content = file_get_contents($envPath);
    if (!preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
        return $default;
    }

    return trim($matches[1], " \t\n\r\0\x0B\"'");
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Check if the application is installed
if (!file_exists(__DIR__ . '/../storage/installed.json') && !str_contains($_SERVER['REQUEST_URI'], '/install')) {
    if (frontControllerEnvValue('APP_ENV') === 'production' && frontControllerEnvValue('INSTALLER_ENABLED', 'false') !== 'true') {
        http_response_code(503);
        echo 'Application installation marker is missing.';
        exit;
    }

    // $installUrl = str_replace(basename($_SERVER['SCRIPT_NAME']), 'install/index.php', $_SERVER['SCRIPT_NAME']);
    $url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'] . '/install/index.php';
    header("Location: $url");
    exit;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
