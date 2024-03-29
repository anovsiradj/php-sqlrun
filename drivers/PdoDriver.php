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
	public function connect($connect)
	{
		$this->connect = $connect;
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
}
