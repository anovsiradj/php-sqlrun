<?php

use anovsiradj\sqlrun\drivers\PdoDriver;
use anovsiradj\sqlrun\runners\FileRunner;

require __DIR__ . '/env.php';

// Migration directory: CLI arg > env var > .env default
$dir = $argv[1] ?? $_ENV['MIGRATION_DIR'] ?? getenv('MIGRATION_DIR') ?? __DIR__ . '/mysql';

$connect = new PDO($env['PDO_DSN'], $env['PDO_USERNAME'] ?? null, $env['PDO_PASSWORD'] ?? null);

$driver = new PdoDriver($connect);
$runner = new FileRunner;
$runner->driver($driver);

$runner->run("{$dir}/structures.sql", false);
$runner->runDir("{$dir}/patches");
$runner->run("{$dir}/contents.sql", false);

dump($driver->logs('error'));
