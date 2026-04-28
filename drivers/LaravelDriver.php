<?php

namespace anovsiradj\sqlrun\drivers;

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
		if (empty($table) && isset($this->migrationTable)) {
			$table = $this->migrationTable;
		}
		if (empty($table)) {
			$table = 'migrations'; // DEFAULT
		}
		return $table;
	}

	public function query($sql): bool
	{
		if (empty($sql) || trim($sql) === '') {
			$this->log([
				'query' => $sql,
				'error' => 'empty',
			]);
			return false;
		}

		try {
			$result = $this->connect->unprepared($sql);
			$this->log([
				'query' => $sql,
				'result' => $result,
			]);
			return $result;
		} catch (QueryException $e) {
			$this->log([
				'query' => $sql,
			], $e);
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
