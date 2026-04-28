<?php

namespace anovsiradj\sqlrun\drivers;

use Exception;
use Throwable;

class Driver
{
	public $logs = [];

	public $migration = true;
	public $migrationTable = null;

	public function log(array $data = [], $exception = null)
	{
		if ($exception instanceof Exception || $exception instanceof Throwable) {
			$data = array_merge([
				'error' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'code' => $exception->getCode(),
			], $data);
		}

		$this->logs[] = array_merge([
			'driver' => static::class,
			'query' => null,
			'result' => null,
			'context' => null,
			'error' => null,
			'file' => null,
			'line' => null,
			'code' => null,
		], $data);
	}

	public function logs($key)
	{
		$logs = array_map(fn($log) => $log[$key] ?? null, $this->logs);
		$logs = array_filter($logs, fn($log) => isset($log));
		return $logs;
	}

	/**
	 * @return bool
	 */
	public function query($sql)
	{
		$this->log([
			'query' => $sql,
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
		]);
		return false;
	}

	public function fetchOne($sql, $params = [])
	{
		$this->log([
			'query' => $sql,
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
			'context' => ['params' => $params],
		]);
		return null;
	}

	public function fetchAll($sql, $params = [])
	{
		$this->log([
			'query' => $sql,
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
			'context' => ['params' => $params],
		]);
		return null;
	}

	public function fetchScalar($sql, $params = [])
	{
		$this->log([
			'query' => $sql,
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
			'context' => ['params' => $params],
		]);
		return null;
	}

	public function migrationExist($name)
	{
		$this->log([
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
			'context' => ['name' => $name],
		]);
		return null;
	}

	public function migrationInsert($name)
	{
		$this->log([
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
			'context' => ['name' => $name],
		]);
		return null;
	}

	public function migrationTable()
	{
		$this->log([
			'error' => sprintf('[UNIMPLEMENTED] %s', __FUNCTION__),
		]);
		return null;
	}
}
