<?php

namespace App\Console\Commands;

use anovsiradj\sqlrun\drivers\LaravelDriver;
use anovsiradj\sqlrun\runners\FileRunner;

class SqlrunFile extends \Illuminate\Console\Command
{
	protected $signature = 'sqlrun:file';

	public function handle()
	{
		$dir = getenv('MIGRATION_DIR') ?: __DIR__ . '/../../../tests/mysql';
		$runner = new FileRunner(new LaravelDriver);
		$runner->driver->migrationInit();
		$runner->run("{$dir}/structures.sql", false);
		$runner->runDir("{$dir}/patches");
		$runner->run("{$dir}/contents.sql", false);

		dump($runner->driver->logs('error'));
	}
}
