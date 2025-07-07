<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


// BASE_URL
defined("BASE_URL") or define("BASE_URL", $_ENV['APP_URL']);

// BASE_PATH
defined("BASE_PATH") or define("BASE_PATH", realpath($_SERVER['DOCUMENT_ROOT'] . '/'));