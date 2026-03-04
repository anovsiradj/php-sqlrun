# mempersiapkan definisi koneksi database

```sh
cd tests
cp .env.default .env

php ./pdo.php
```

# eksekusi migrasi yii2

harus diingat, yii2 tidak ada driver mariadb, jadi harus selalu pakai mysql.

```sh
# sekali (generate migration table)
php ./yii2.php migrate
php ./yii2.php migrate/fresh --interactive=0

# berkali
php ./yii2.php sqlrun/file
```

# eksekusi migrasi laravel

```sh
# sekali (generate migration table)
php ./laravel.php migrate
php ./laravel.php migrate:fresh
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
