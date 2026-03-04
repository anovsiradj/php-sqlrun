<?php

namespace anovsiradj\sqlrun\drivers;

use Yii;
use yii\db\Connection;

class Yii2Driver extends Driver
{
	public Connection $connect;

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

	public function query($sql)
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
}
