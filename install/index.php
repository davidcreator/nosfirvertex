<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../system/bootstrap.php';

$app = new AureaVertex\System\Engine\Application('install');
$app->run();
