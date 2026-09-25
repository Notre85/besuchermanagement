#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

$expired = (new App\PlannedVisit($pdo))->expire();
fwrite(STDOUT, sprintf("%d geplante Besuche abgelaufen.\n", $expired));
