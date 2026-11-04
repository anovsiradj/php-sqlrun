<?php

require __DIR__ . '/env.php';

// Bootstrap: populate $_ENV from process environment (so Laravel's env() works)
foreach (getenv() as $key => $val) {
    $_ENV[$key] = $val;
}

spl_autoload_register(function ($classname) {
	if (strpos($classname, 'App\\') !== 0) {
		return false;
	}
	$filename = $classname;
	$filename = str_replace('App\\', 'app/', $filename);
	$filename = str_replace('\\', '/', $filename);
	$filename = __DIR__ . "/laravel/{$filename}.php";
	if (file_exists($filename) && is_file($filename)) {
		require $filename;
		return true;
	}
	return false;
});

define('LARAVEL_START', microtime(true));

$app = require_once __DIR__ . '/laravel/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->handle(
	$input = new Symfony\Component\Console\Input\ArgvInput,
	new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);
exit($status);
