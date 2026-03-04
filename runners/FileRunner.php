<?php

namespace anovsiradj\sqlrun\runners;

use Exception;

class FileRunner extends Runner
{
	public function run($file, $migration = true)
	{
		if (empty($file) || trim($file) === '') {
			echo '[SKIP] file is empty', PHP_EOL;
			return;
		}

		if (!file_exists($file) || !is_file($file)) {
			echo "[SKIP] file not exist", PHP_EOL;
			return;
		}

		if (preg_match('/\.sql$/i', $file) === 1) {
			$this->runSql($file, $migration);
		} elseif (preg_match('/\.php$/i', $file) === 1) {
			$this->runPhp($file, $migration);
		} else {
			echo "[SKIP] unknown file {$file}", PHP_EOL;
		}
	}

	public function runDir($dir, $migration = true)
	{
		if (empty($dir) || trim($dir) === '') {
			echo '[SKIP] dir is empty', PHP_EOL;
			return;
		}

		if (!file_exists($dir) || !is_dir($dir)) {
			echo '[SKIP] dir not exist', PHP_EOL;
			return;
		}

		$files = glob($dir . '/{,*/}*', GLOB_BRACE);
		foreach ($files as $file) {
			$this->run($file, $migration);
		}
	}

	public function runSql($file, $migration = true)
	{
		if ($migration && $this->driver->migrationExist(basename($file))) {
			echo "[MGRT] {$file}", PHP_EOL;
			return;
		}

		$sql = file_get_contents($file);

		if ($this->driver->query($sql)) {
			if ($migration) {
				$this->driver->migrationInsert(basename($file));
			}
			echo "[DONE] {$file}", PHP_EOL;
		} else {
			echo "[FAIL] {$file}", PHP_EOL;
		}
	}

	public function runPhp($file, $migration = true)
	{
		if ($migration && $this->driver->migrationExist(basename($file))) {
			echo "[MGRT] {$file}", PHP_EOL;
			return;
		}

		$closure = function () use ($file) {
			extract((array) $this);
			return (require $file);
		};

		try {
			$result = $closure();

			if ($result === false) {
				echo "[FAIL] {$file}", PHP_EOL;
			} else {
				if ($migration) {
					$this->driver->migrationInsert(basename($file));
				}
				echo "[DONE] {$file}", PHP_EOL;
			}
		} catch (Exception $e) {
			$this->driver->logs[] = ['error' => $e->getMessage()];
			echo "[FAIL] {$file}", PHP_EOL;
		}
	}
}
