<?php

if (getenv("NODE_ENV") !== "production") {
    require_once __DIR__ . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
    $dotenv->load();
} else {
    $_ENV['GEMINI_API_KEY'] = getenv('GEMINI_API_KEY');
    $_ENV['APP_BASE_URL'] = getenv('APP_BASE_URL');
}
