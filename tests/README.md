# mempersiapkan definisi koneksi database

```sh
cp .env.default .env

php ./pdo.php
```

# eksekusi migrasi yii2

```sh
# sekali (untuk generate tabel yii2_migrations)
php ./tests/yii2.php migrate

# berkali
php ./yii2.php sqlrun/file
```

# eksekusi migrasi laravel

```sh
# sekali (untuk generate tabel laravel_migrations)
php ./laravel.php migrate
php ./laravel.php migrate:install

# berkali
php ./laravel.php sqlrun:file
```

# optimasi migrasi laravel

supaya upsert lebih efektif

```sql
ALTER TABLE `migrations`
ADD UNIQUE `migration` (`migration`);

ALTER TABLE `migrations`
CHANGE `batch` `batch` int(11) NOT NULL DEFAULT '0' AFTER `migration`;
```
