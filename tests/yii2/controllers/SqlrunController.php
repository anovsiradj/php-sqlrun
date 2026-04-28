<?php

namespace sqlrun\yii2\controllers;

use anovsiradj\sqlrun\drivers\Yii2Driver;
use anovsiradj\sqlrun\runners\FileRunner;

class SqlrunController extends \yii\console\Controller
{
	public function actionFile()
	{
		$runner = new FileRunner(new Yii2Driver);

		$dir = $_ENV['MIGRATION_DIR'];
		$runner->run("{$dir}/structures.sql", false);
		$runner->runDir("{$dir}/patches");
		$runner->run("{$dir}/contents.sql", false);

		dump($runner->driver->logs('error'));
	}
}
