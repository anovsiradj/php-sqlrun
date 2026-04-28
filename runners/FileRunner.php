<?php

namespace anovsiradj\sqlrun\runners;

use Exception;

class FileRunner extends Runner
{
	public ?string $migrationGroup = null;

	public function migrationGroup($group)
	{
		if (isset($group) && trim($group) !== '') {
			$this->migrationGroup = $group;
		}
	}

	protected function migrationName($file, bool $check = true): string
	{
		$group = $this->migrationGroup;
		$name = basename($file);
		if ($check && isset($group) && trim($group) !== '') {
			$name = "{$group}:{$name}";
		}
		return $name;
	}

	protected function migrationExist($file): bool
	{
		$name = $this->migrationName($file);
		if ($this->driver->migrationExist($name)) {
			return true;
		}

		// Backward compatible: when group is enabled, still honor old basename key.
		$name = $this->migrationName($file, false);
		if ($this->driver->migrationExist($name)) {
			return true;
		}

		return false;
	}

	public function run($file, $migrate = true)
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
			$this->runSql($file, $migrate);
		} elseif (preg_match('/\.php$/i', $file) === 1) {
			$this->runPhp($file, $migrate);
		} else {
			echo "[SKIP] unknown {$file}", PHP_EOL;
		}
	}

	public function runDir($dir, $migrate = true)
	{
		if (empty($dir) || trim($dir) === '') {
			echo '[SKIP] dir is empty', PHP_EOL;
			return;
		}

		if (!file_exists($dir) || !is_dir($dir)) {
			echo '[SKIP] dir not exist', PHP_EOL;
			return;
		}

		$files = $this->migrationFiles($dir);
		foreach ($files as $file) {
			$this->run($file, $migrate);
		}
	}

	protected function migrationFiles($dir): array
	{
		$files = glob($dir . '/{,*/}*', GLOB_BRACE);
		$files = array_filter($files, function ($file) {
			if (!is_file($file)) {
				return false;
			}
			return preg_match('/\.(sql|php)$/i', $file) === 1;
		});

		usort($files, function ($a, $b) {
			$basenameA = basename($a);
			$basenameB = basename($b);
			$compare = strnatcasecmp($basenameA, $basenameB);
			if ($compare !== 0) {
				return $compare;
			}
			return strnatcasecmp($a, $b);
		});

		return $files;
	}

	public function runSql($file, $migrate = true)
	{
		$name = $this->migrationName($file);
		if ($migrate && $this->migrationExist($file)) {
			echo "[MGRT] {$file}", PHP_EOL;
			return;
		}

		$sql = file_get_contents($file);

		if ($this->driver->query($sql)) {
			if ($migrate) {
				$this->driver->migrationInsert($name);
			}
			echo "[DONE] {$file}", PHP_EOL;
		} else {
			echo "[FAIL] {$file}", PHP_EOL;
		}
	}

	public function runPhp($file, $migrate = true)
	{
		$name = $this->migrationName($file);
		if ($migrate && $this->migrationExist($file)) {
			echo "[MGRT] {$file}", PHP_EOL;
			return;
		}

		$php = function () use ($file) {
			extract((array) $this);
			return (require $file);
		};

		try {
			$result = $php();

			if ($result === false) {
				echo "[FAIL] {$file}", PHP_EOL;
			} else {
				if ($migrate) {
					$this->driver->migrationInsert($name);
				}
				echo "[DONE] {$file}", PHP_EOL;
			}
		} catch (Exception $e) {
			$this->driver->log([
				'context' => [
					'name' => $name,
					'file' => $file,
				],
			], $e);
			echo "[FAIL] {$file}", PHP_EOL;
		}
	}
}
