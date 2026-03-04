<?php

namespace sqlrun\yii2\controllers;

use anovsiradj\sqlrun\drivers\Yii2Driver;
use anovsiradj\sqlrun\runners\FileRunner;

class SqlrunController extends \yii\console\Controller
{
	public function actionFile()
	{
		$driver = new Yii2Driver;
		$runner = new FileRunner;
		$runner->driver($driver);

		$runner->run("{$_ENV['FILE_DIR']}/structures.sql");
		$runner->runDir("{$_ENV['FILE_DIR']}/patches");
		$runner->run("{$_ENV['FILE_DIR']}/contents.sql");

		dump($driver->logs('error'));
	}
}
