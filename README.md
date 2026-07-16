# sqlrun

PHP migrasi SQL satu-arah, Gak Pake Ribet.

### pemakaian

lihat `./tests/*.php`

### `.sql` / `.php`

### catatan

khusus driver Yii2,PDO tidak ada driver `mariadb` jadi harus pakai `mysql`.

### rencana

- `archive`: catat file sql yang belum tercatat
- `connect`: cek koneksi database
- `inspect`: (aku lupa untuk apa ini)
- `schema`: expor skema jadi file sql

### migration Group

- default: `basename(file)` (backward compatible)
- dengan group: `{GROUP}:{BASENAME}` via `FileRunner::migrationGroup('GROUP')`

### ordering & filter

- `runDir()` hanya eksekusi file `.sql` / `.php`
- urutan eksekusi eksplisit: alphanumeric by filename (tie-breaker: full path)

### framework defaults

- PDO: migration table init dari `./migrations/pdo/{driver}.sql`
- Yii2: migration table baca `controllerMap['migrate']['migrationTable']`, fallback `migration`
- Laravel: migration table baca `config('database.migrations')` (string/array `['table' => ...]`)

## License

MIT
