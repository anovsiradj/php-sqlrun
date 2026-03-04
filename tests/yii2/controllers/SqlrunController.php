<?php

namespace sqlrun\yii2\controllers;

use anovsiradj\sqlrun\drivers\Yii2Driver;
use anovsiradj\sqlrun\runners\FileRunner;

class SqlrunController extends \yii\console\Controller
{
	public function actionFile()
	{
		$runner = new FileRunner(new Yii2Driver);

		$runner->run("{$_ENV['FILE_DIR']}/structures.sql", false);
		$runner->runDir("{$_ENV['FILE_DIR']}/patches");
		$runner->run("{$_ENV['FILE_DIR']}/contents.sql", false);

		dump($runner->driver->logs('error'));
	}
}
