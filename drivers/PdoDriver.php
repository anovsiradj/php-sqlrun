<?php

namespace anovsiradj\sqlrun\drivers;

use Exception;
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
			$result = $this->connect->exec($sql);

			if ($result !== false) {
				$this->log([
					'query' => $sql,
					'result' => $result,
				]);
			} else {
				$error = $this->connect->errorInfo();
				$this->log([
					'query' => $sql,
					'result' => $result,
					'error' => $error[2] ?? null,
					'code' => $error[1] ?? null,
				]);
			}
			return ($result !== false);
		} catch (PDOException $e) {
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

	public function migrationInit(): bool
	{
		$driver = $this->connect->getAttribute(PDO::ATTR_DRIVER_NAME);
		$file = __DIR__ . "/../migrations/pdo/{$driver}.sql";
		if (!file_exists($file) || !is_file($file)) {
			$this->log(['message' => "migration init file not found: {$file}"]);
			return false;
		}

		$sql = file_get_contents($file);
		if (empty($sql) || trim($sql) === '') {
			$this->log(['message' => 'migration init sql is empty']);
			return false;
		}

		return $this->query($sql);
	}
}
