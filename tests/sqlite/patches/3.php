<?php
/**
 * @var \anovsiradj\sqlrun\drivers\Driver $driver
 */

$driver->query(<<<SQL
	CREATE UNIQUE INDEX `username_unq` ON `user` (`username`);
SQL);

$driver->query(<<<SQL
	CREATE UNIQUE INDEX `email_unq` ON `user` (`email`);
	CREATE UNIQUE INDEX `phone_unq` ON `user` (`phone`);
SQL);
