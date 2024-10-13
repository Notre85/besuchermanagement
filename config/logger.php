<?php
// config/logger.php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . '/../vendor/autoload.php';

$log = new Logger('besuchermanagement');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

function get_logger() {
    global $log;
    return $log;
}