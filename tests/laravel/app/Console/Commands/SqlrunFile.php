<?php

namespace App\Console\Commands;

use anovsiradj\sqlrun\drivers\LaravelDriver;
use anovsiradj\sqlrun\runners\FileRunner;

class SqlrunFile extends \Illuminate\Console\Command
{
	protected $signature = 'sqlrun:file';

	public function handle()
	{
		$dir = $_ENV['MIGRATION_DIR'];
		$runner = new FileRunner(new LaravelDriver);
		$runner->run("{$dir}/structures.sql", false);
		$runner->runDir("{$dir}/patches");
		$runner->run("{$dir}/contents.sql", false);

		dump($runner->driver->logs('error'));
	}
}
