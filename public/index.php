<?php

use App\App;

require __DIR__ . '/../vendor/autoload.php';

$app = new App(require __DIR__ . '/../config/config.php');
$app->run();
