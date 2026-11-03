<?php

$table = 'laravel_migrations';

$mysql = [
	'host' => env('LARAVEL_HOST'),
	'port' => env('LARAVEL_PORT'),
	'database' => env('LARAVEL_DBNAME'),
	'username' => env('LARAVEL_USERNAME'),
	'password' => env('LARAVEL_PASSWORD'),
	'prefix_indexes' => true,
	'strict' => true,
	'engine' => null,
];

$version = intval(env('LARAVEL_VERSION', 999));

$config = [
	'default' => env('LARAVEL_DRIVER'),

	'connections' => [
		'sqlite' => [
			'driver' => 'sqlite',
			'database' => env('SQLITE_FILE'),
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
			'host' => env('LARAVEL_HOST'),
			'port' => env('LARAVEL_PORT'),
			'database' => env('LARAVEL_DBNAME'),
			'username' => env('LARAVEL_USERNAME'),
			'password' => env('LARAVEL_PASSWORD'),
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
