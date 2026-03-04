mempersiapkan definisi koneksi database

```sh
cp .env.default .env

php ./pdo.php
```

eksekusi migrasi yii2

```sh
# sekali (untuk generate tabel yii2_migrations)
php ./tests/yii2.php migrate

# berkali
php ./yii2.php sqlrun/file
```

eksekusi migrasi laravel

```sh
# sekali (untuk generate tabel laravel_migrations)
php ./laravel.php migrate
php ./laravel.php migrate:install

# berkali
php ./laravel.php sqlrun:file
```
