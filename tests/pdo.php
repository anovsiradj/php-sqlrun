<?php

use anovsiradj\sqlrun\drivers\PdoDriver;
use anovsiradj\sqlrun\runners\FileRunner;

require __DIR__ . '/env.php';

$connect = new PDO($env['PDO_DSN'], $env['PDO_USERNAME'], $env['PDO_PASSWORD']);
// dd($env);

$driver = new PdoDriver($connect);
$runner = new FileRunner;
$runner->driver($driver);

$dir = $_ENV['MIGRATION_DIR'];
$runner->run("{$dir}/structures.sql", false);
$runner->runDir("{$dir}/patches");
$runner->run("{$dir}/contents.sql", false);

// dump($driver->logs);
dump($driver->logs('error'));
