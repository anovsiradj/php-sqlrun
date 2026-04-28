<?php

namespace anovsiradj\sqlrun\drivers;

use Yii;
use yii\db\Connection;

class Yii2Driver extends Driver
{
	public Connection $connect;

	/**
	 * @param Connection $connect
	 */
	public function __construct($connect = null)
	{
		if (empty($connect) && Yii::$app->has('db')) {
			$connect = Yii::$app->get('db');
		}
		if ($connect) {
			$this->connect($connect);
		}
	}

	/**
	 * @param Connection $connect
	 */
	public function connect($connect)
	{
		$this->connect = $connect;
	}

	public function migrationTable()
	{
		$table = Yii::$app->controllerMap['migrate']['migrationTable'] ?? null;
		if (empty($table) && isset($this->migrationTable)) {
			$table = $this->migrationTable;
		}
		if (empty($table)) {
			$table = 'migration'; // DEFAULT
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
			$result = $this->connect->masterPdo->exec($sql);
			if ($result === false) {
				$error = $this->connect->masterPdo->errorInfo();
				$this->log([
					'query' => $sql,
					'result' => $result,
					'error' => $error[2] ?? null,
					'code' => $error[1] ?? null,
				]);

				return false;
			}

			$this->log([
				'query' => $sql,
				'result' => $result,
			]);

			return true;
		} catch (\yii\db\Exception $e) {
			$this->log([
				'query' => $sql,
			], $e);

			return false;
		} catch (\PDOException $e) {
			$this->log([
				'query' => $sql,
			], $e);

			return false;
		} catch (\Exception $e) {
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
		$sql = <<<SQL
			SELECT 1 FROM {$table} WHERE version = :version
		SQL;
		$result = $this->connect->createCommand($sql)
			->bindValue(':version', $name)
			->queryScalar();

		return (bool) $result;
	}

	public function migrationInsert($name): bool
	{
		if (!$this->migration) {
			return false;
		}

		$table = $this->migrationTable();
		$result = $this->connect
			->createCommand(<<<SQL
				INSERT INTO {$table} (version, apply_time)
				VALUES (:version, :apply_time)
			SQL)->bindValues([
				':version' => $name,
				':apply_time' => time(),
			])
			->execute();
		return (bool) $result;
	}
}
