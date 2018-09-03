<?php


if (!defined('DS')) define('DS', '/');

if (!defined('ROOT_DIR')) define('ROOT_DIR', dirname(dirname(__FILE__)) . DS);

require ROOT_DIR . 'vendor/autoload.php';

require ROOT_DIR . 'config/Settings.php';

require ROOT_DIR . 'functions.php';


if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

if (!defined('HTTP_HOST')) define('HTTP_HOST', env('HTTP_HOST'));

if (!defined('URL_BASE')) {
    $s = null;
    if (env('HTTPS')) $s = 's';
    define('URL_BASE', 'http' . $s . '://' . HTTP_HOST . DS . Settings::SUB_DIR);
}

if (!defined('WEB_ROOT')) {
    define('WEB_ROOT', URL_BASE . Settings::ADDITIONAL_DIR);
}

$request = new Request();

$dispatcher = new Dispatcher($request);
$dispatcher->dispatch();
