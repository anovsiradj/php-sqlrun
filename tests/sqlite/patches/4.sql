-- patch 4
ALTER TABLE `table3` ADD COLUMN `created_at` DATETIME NULL;
ALTER TABLE `table3` ADD COLUMN `updated_at` DATETIME NULL;

ALTER TABLE `table3` ADD COLUMN `created_by` INTEGER NULL;
ALTER TABLE `table3` ADD COLUMN `updated_by` INTEGER NULL;

CREATE UNIQUE INDEX `name_unq` ON `role` (`name`);
