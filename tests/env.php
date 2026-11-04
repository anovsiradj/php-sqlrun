<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->usePutenv(true);

// Load .env unless orchestrator already set vars via proc_open (check getenv or $_ENV)
if (getenv('MIGRATION_DIR') === false && !isset($_ENV['MIGRATION_DIR'])) {
	$dotenv->load(__DIR__ . '/.env');
}

// Build $env from getenv() and $_ENV (proc_open env wins over .env)
$envData = [];
foreach (getenv() as $key => $val) {
	$envData[$key] = $val;
}
foreach ($_ENV as $key => $val) {
	$envData[$key] = $val;
}

// Apply mariadb-first defaults if not set
$defaults = [
	'MARIADB_HOST' => '127.0.0.1',
	'MARIADB_PORT' => 3306,
	'MARIADB_DBNAME' => 'sqlrun',
	'PGSQL_HOST' => '127.0.0.1',
	'PGSQL_PORT' => 5432,
	'PGSQL_DBNAME' => 'sqlrun',
];
$envData = array_merge($defaults, $envData);

$mysqlDefaults = [
	'MYSQL_HOST' => $envData['MARIADB_HOST'],
	'MYSQL_PORT' => $envData['MARIADB_PORT'],
	'MYSQL_DBNAME' => $envData['MARIADB_DBNAME'],
];
$envData = array_merge($mysqlDefaults, $envData);

$env = new ArrayObject($envData, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
