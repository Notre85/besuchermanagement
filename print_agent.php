<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/csrf.php';

(new App\Controllers\PrintAgentController($pdo))->handle();
