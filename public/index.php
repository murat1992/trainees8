<?php

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../classes/application.php';

$app = new CApplication;
$app->handle();

?>