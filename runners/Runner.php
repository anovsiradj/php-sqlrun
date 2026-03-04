<?php

namespace anovsiradj\sqlrun\runners;

use anovsiradj\sqlrun\drivers\Driver;

class Runner
{
	public Driver $driver;

	public function __construct($driver = null)
	{
		if ($driver) {
			$this->driver($driver);
		}
	}

	public function driver($driver)
	{
		$this->driver = $driver;
	}
}
