<?php

namespace anovsiradj\sqlrun\drivers;

use PDO;
use PDOException;

class PdoDriver extends Driver
{
	public PDO $connect;

	/**
	 * @param PDO $connect
	 */
	public function __construct($connect = null)
	{
		if ($connect) {
			$this->connect($connect);
		}
	}

	/**
	 * @param PDO $connect
	 */
	public function connect($connect)
	{
		$this->connect = $connect;
		$this->migrationInit();
	}

	public function query($sql)
	{
		if (empty($sql) || trim($sql) === '') {
			$this->logs[] = ['query' => $sql, 'error' => 'empty'];
			return false;
		}

		try {
			$result = $this->connect->exec($sql);

			$log = [
				'query' => $sql,
				'result' => $result,
			];

			if ($result !== false) {
				$this->logs[] = $log;
			} else {
				$this->logs[] = array_merge($log, [
					'error' => $this->connect->errorInfo()[2] ?? null,
				]);
			}
			return ($result !== false);
		} catch (PDOException $e) {
			$this->logs[] = [
				'query' => $sql,
				'error' => $e->errorInfo[2] ?? null,
			];
			return false;
		}
	}

	public function migrationExist($name): bool
	{
		if (!$this->migration) {
			return false;
		}

		$deklar = $this->connect->prepare(<<<SQL
			SELECT 1 from migration
			WHERE id=:id
		SQL);

		$deklar->execute([
			':id' => $name,
		]);

		$result = $deklar->fetchColumn();
		return (bool) $result;
	}

	public function migrationInsert($name): bool
	{
		if (!$this->migration) {
			return false;
		}

		$deklar = $this->connect->prepare(<<<SQL
			INSERT INTO migration (id, at) VALUES (:id, :at)
		SQL);

		$result = $deklar->execute([
			':id' => $name,
			':at' => date('Y-m-d H:i:s'),
		]);

		return (bool) $result;
	}

	/**
	 * @todo database agnostic
	 * @return bool
	 */
	public function migrationInit(): bool
	{
		$sql = <<<'SQL'
		CREATE TABLE IF NOT EXISTS migration (
			`id` VARCHAR(256) PRIMARY KEY,
			`at` DATETIME
		);
		SQL;

		return $this->query($sql);
	}
}
