<?php

namespace anovsiradj\sqlrun\drivers;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class LaravelDriver extends Driver
{
	public ConnectionInterface $connect;

	/**
	 * @param ConnectionInterface $connect
	 */
	public function __construct($connect = null)
	{
		if (empty($connect)) {
			$connect = DB::connection();
		}
		if ($connect) {
			$this->connect($connect);
		}
	}

	/**
	 * @param ConnectionInterface $connect
	 */
	public function connect($connect)
	{
		$this->connect = $connect;
	}

	public function migrationTable()
	{
		$table = config('database.migrations');
		if (is_array($table)) {
			$table = $table['table'] ?? null;
		}

		if (empty($table)) {
			throw new Exception(__FUNCTION__, 1);
		}
		return $table;
	}

	public function query($sql)
	{
		if (empty($sql) || trim($sql) === '') {
			$this->logs[] = ['query' => $sql, 'error' => 'empty'];
			return false;
		}

		try {
			$result = $this->connect->unprepared($sql);
			$this->logs[] = ['query' => $sql, 'result' => $result];
			return $result;
		} catch (QueryException $e) {
			$this->logs[] = ['query' => $sql, 'error' => $e->getMessage()];
			return false;
		}
	}

	public function migrationExist($name): bool
	{
		if (!$this->migration) {
			return false;
		}

		$table = $this->migrationTable();
		$model = $this->connect->table($table)->where('migration', '=', $name)->first();

		return isset($model);
	}

	public function migrationInsert($name): bool
	{
		if (!$this->migration) {
			return false;
		}

		$table = $this->migrationTable();
		return (bool) $this->connect->table($table)->insertOrIgnore([
			'migration' => $name,
			'batch' => time(),
		]);
	}
}
