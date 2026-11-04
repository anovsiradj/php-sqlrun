<?php

/**
 * Automated test orchestrator untuk anovsiradj/sqlrun.
 * Satu perintah: php tests/all.php
 * Satu .env file saja. Scenario switch via vars override terprogram.
 */

// Load autoloader first
require_once __DIR__ . '/../vendor/autoload.php';

// env.php handles .env loading with usePutenv(true)
require_once __DIR__ . '/env.php';

/**
 * Build DSN string
 */
function dsn(string $driver, ArrayObject $env): string
{
	return match ($driver) {
		'sqlite' => "sqlite:{$env->SQLITE_DBFILE}",
		'mysql', 'mariadb' => "mysql:host={$env->MYSQL_HOST};port={$env->MYSQL_PORT};dbname={$env->MYSQL_DBNAME}",
		'pgsql' => "pgsql:host={$env->PGSQL_HOST};port={$env->PGSQL_PORT};dbname={$env->PGSQL_DBNAME}",
		default => throw new InvalidArgumentException("Unknown driver: $driver"),
	};
}

/**
 * Build scenario - data driven
 */
function scenario(string $name, string $framework, string $driver, string $dir, array $extra = []): array
{
	global $env;

	$vars = [
		'MIGRATION_DIR' => __DIR__ . '/' . $dir,
		'SQLITE_DBFILE' => $env->SQLITE_DBFILE,
	];

	// Framework-specific vars
	$vars["{$framework}_DRIVER"] = $driver;

	if ($framework === 'LARAVEL') {
		$vars['LARAVEL_VERSION'] = '13';
		$vars += match ($driver) {
			'mariadb' => [
				'LARAVEL_HOST' => $env->MARIADB_HOST,
				'LARAVEL_PORT' => $env->MARIADB_PORT,
				'LARAVEL_USERNAME' => $env->MARIADB_USERNAME,
				'LARAVEL_PASSWORD' => $env->MARIADB_PASSWORD,
				'LARAVEL_DBNAME' => $env->MARIADB_DBNAME,
			],
			'pgsql' => [
				'LARAVEL_HOST' => $env->PGSQL_HOST,
				'LARAVEL_PORT' => $env->PGSQL_PORT,
				'LARAVEL_USERNAME' => $env->PGSQL_USERNAME,
				'LARAVEL_PASSWORD' => $env->PGSQL_PASSWORD,
				'LARAVEL_DBNAME' => $env->PGSQL_DBNAME,
			],
			'sqlite' => [],
			default => throw new InvalidArgumentException("Unknown Laravel driver: $driver"),
		};
		$cmds = ['laravel.php sqlrun:file'];
	} elseif ($framework === 'PDO') {
		$vars['PDO_DSN'] = dsn($driver, $env);
		if ($driver !== 'sqlite') {
			$vars['PDO_USERNAME'] = $driver === 'mysql' ? $env->MYSQL_USERNAME : $env->PGSQL_USERNAME;
			$vars['PDO_PASSWORD'] = $driver === 'mysql' ? $env->MYSQL_PASSWORD : $env->PGSQL_PASSWORD;
		}
		$cmds = ['pdo.php'];
	} elseif ($framework === 'YII2') {
		$vars['YII2_DSN'] = dsn($driver, $env);
		$vars['YII2_CUSTOM'] = '1';
		if ($driver !== 'sqlite') {
			$vars['YII2_USERNAME'] = $driver === 'mysql' ? $env->MYSQL_USERNAME : $env->PGSQL_USERNAME;
			$vars['YII2_PASSWORD'] = $driver === 'mysql' ? $env->MYSQL_PASSWORD : $env->PGSQL_PASSWORD;
			$vars['YII2_DBNAME'] = $driver === 'mysql' ? $env->MYSQL_DBNAME : $env->PGSQL_DBNAME;
		}
		$cmds = [
			'yii2.php migrate/fresh --interactive=0',
			'yii2.php sqlrun/file',
		];
	}

	$vars = array_merge($vars, $extra);

	return ['name' => $name, 'vars' => $vars, 'cmds' => $cmds];
}

$scenarios = [
	// PDO
	scenario('PDO + SQLite',     'PDO', 'sqlite', 'sqlite'),
	scenario('PDO + MariaDB',    'PDO', 'mysql',  'mysql'),
	scenario('PDO + PostgreSQL', 'PDO', 'pgsql',  'pgsql'),

	// Laravel
	scenario('Laravel + MariaDB',    'LARAVEL', 'mariadb', 'mysql'),
	scenario('Laravel + PostgreSQL', 'LARAVEL', 'pgsql',   'pgsql'),
	scenario('Laravel + SQLite',     'LARAVEL', 'sqlite',  'sqlite'),

	// Yii2
	scenario('Yii2 + MySQL',     'YII2', 'mysql',  'mysql'),
	scenario('Yii2 + PostgreSQL', 'YII2', 'pgsql',  'pgsql'),
	scenario('Yii2 + SQLite',    'YII2', 'sqlite', 'sqlite'),
];

$results = [];
$passed = 0;
$failed = 0;

foreach ($scenarios as $scenario) {
	echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	echo "Running: {$scenario['name']}\n";
	echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

	// Build environment for child process
	$envChild = $_ENV;
	foreach ($scenario['vars'] as $key => $val) {
		$envChild[$key] = $val;
	}

	// Run commands
	$allPassed = true;
	$lastExitCode = 0;
	$output = '';

	foreach ($scenario['cmds'] as $cmd) {
		$fullCmd = PHP_BINARY . ' ' . __DIR__ . '/' . $cmd;
		$process = proc_open($fullCmd, [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		], $pipes, null, $envChild);

		if (!is_resource($process)) {
			echo "[ERROR] Failed to run: $fullCmd\n";
			$allPassed = false;
			$lastExitCode = 1;
			break;
		}

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exitCode = proc_close($process);

		$output .= $stdout;
		if ($stderr) {
			$output .= "STDERR ($cmd):\n{$stderr}\n";
		}

		if ($exitCode !== 0) {
			$allPassed = false;
			$lastExitCode = $exitCode;
			break;
		}
	}

	echo $output;

	if ($allPassed) {
		echo "[PASS] {$scenario['name']}\n";
		$passed++;
		$results[] = ['name' => $scenario['name'], 'status' => 'PASS'];
	} else {
		echo "[FAIL] {$scenario['name']} (exit code: {$lastExitCode})\n";
		$failed++;
		$results[] = ['name' => $scenario['name'], 'status' => 'FAIL', 'exit_code' => $lastExitCode];
	}
}

echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . count($scenarios) . "\n";

exit($failed > 0 ? 1 : 0);
