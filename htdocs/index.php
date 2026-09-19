<?php
require_once __DIR__ . '/app/core/functions.php';

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Router.php';

$url = $_GET['url'] ?? '';
$url = trim($url, '/');

$router = new Router();
$router->dispatch($url);
