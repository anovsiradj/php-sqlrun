<?php

$table = 'laravel_migrations';

$mysql = [
	'host' => env('LARAVEL_HOST', 'localhost'),
	'port' => env('LARAVEL_PORT', '3306'),
	'database' => env('LARAVEL_DBNAME', 'sqlrun'),
	'username' => env('LARAVEL_USERNAME', 'root'),
	'password' => env('LARAVEL_PASSWORD', 'password'),
	'prefix_indexes' => true,
	'strict' => true,
	'engine' => null,
];

$version = (int) env('LARAVEL_VERSION', 999);

$config = [
	'default' => env('LARAVEL_DRIVER', 'mysql'),

	'connections' => [
		'sqlite' => [
			'driver' => 'sqlite',
			'database' => env('SQLITE_DBFILE', __DIR__ . '/../../../tests/.sqlite'),
			'foreign_key_constraints' => env('LARAVEL_FOREIGN_KEYS', true),
		],
		'mariadb' => array_merge($mysql, [
			'driver' => 'mariadb',
		]),
		'mysql' => array_merge($mysql, [
			'driver' => 'mysql',
		]),
		'pgsql' => [
			'driver' => 'pgsql',
			'host' => env('LARAVEL_HOST', 'localhost'),
			'port' => env('LARAVEL_PORT', '5432'),
			'database' => env('LARAVEL_DBNAME', 'sqlrun'),
			'username' => env('LARAVEL_USERNAME', 'postgres'),
			'password' => env('LARAVEL_PASSWORD', 'password'),
			'charset' => 'utf8',
			'prefix_indexes' => true,
			'schema' => 'public',
			'sslmode' => 'disable',
		],
	],
];

if ($version <= 10) {
	$config = array_merge($config, [
		'migrations' => $table,
	]);
}
if ($version >= 11) {
	$config = array_merge($config, [
		'migrations' => [
			'table' => $table,
			'update_date_on_publish' => true,
		],
	]);
}

return $config;
