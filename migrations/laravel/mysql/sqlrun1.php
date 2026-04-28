<?php

/**
 * @var \anovsiradj\sqlrun\drivers\Driver $driver
 * 
 * optimasi tabel migrasi punya laravel
 */

$driver->query(<<<SQL
	ALTER TABLE `{$driver->migrationTable()}` ADD UNIQUE `migration` (`migration`);
SQL);

$driver->query(<<<SQL
	ALTER TABLE `{$driver->migrationTable()}` CHANGE `batch` `batch` int(11) NOT NULL DEFAULT '0' AFTER `migration`;
SQL);
