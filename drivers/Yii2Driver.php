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

	public function migrationTable(): string
	{
		$table = Yii::$app->controllerMap['migrate']['migrationTable'] ?? null;
		$table ??= 'migrations';
		return $table;
	}

	public function query($sql): bool
	{
		if (empty($sql) || trim($sql) === '') {
			$this->logs[] = ['query' => $sql, 'error' => 'empty'];
			return false;
		}

		try {
			$result = $this->connect->masterPdo->exec($sql);
			if ($result === false) {
				$this->logs[] = [
					'error' => $this->connect->masterPdo->errorInfo(),
					'result' => $result,
				];

				return false;
			}

			return true;
		} catch (\yii\db\Exception $e) {
			$this->logs[] = [
				'query' => $sql,
				'catch' => \yii\db\Exception::class,
				'error' => $e->getMessage(),
			];

			return false;
		} catch (\PDOException $e) {
			$this->logs[] = [
				'query' => $sql,
				'catch' => \PDOException::class,
				'error' => $e->getMessage(),
			];

			return false;
		} catch (\Exception $e) {
			$this->logs[] = [
				'query' => $sql,
				'catch' => get_class($e),
			];

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
