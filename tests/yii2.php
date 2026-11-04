<?php

require __DIR__ . '/env.php';

define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// using custom migrate config or not
$custom = filter_var($_ENV['YII2_CUSTOM'] ?? getenv('YII2_CUSTOM') ?? 'true', FILTER_VALIDATE_BOOLEAN);

$config = [
	'id' => 'sqlrun',
	'name' => 'sqlrun',
	'basePath' => __DIR__,
	'controllerPath' => realpath(__DIR__ . '/yii2/controllers'), // why this not work?
	'controllerNamespace' => 'sqlrun\yii2\controllers',
	'aliases' => [
		'@sqlrun' => __DIR__, // why must use this?
	],
	'components' => [
		'db' => [
			'class' => yii\db\Connection::class,
			'dsn' => $env['YII2_DSN'],
			'username' => $env['YII2_USERNAME'] ?? null,
			'password' => $env['YII2_PASSWORD'] ?? null,
		],
	],
];

if ($custom) {
	$config = array_merge($config, [
		'controllerMap' => [
			'migrate' => [
				'class' => yii\console\controllers\MigrateController::class,
				'migrationTable' => 'yii2_migrations',
			],
		],
	]);
}

$app = new yii\console\Application($config);

$die = $app->run();
exit($die);
