<?php
// config/logger.php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . '/../vendor/autoload.php';

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::WARNING));
?>
