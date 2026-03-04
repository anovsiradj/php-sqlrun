# sqlrun

migrasi SQL satu arah.

### usage

This library provides a lightweight way to execute SQL or PHP
dependency scripts in a controlled one‑directional migration flow.  At its
core there are two concepts:

1. **Driver** – handles the database connection and implements a handful of
   methods such as `query()`, `migrationExist()` and `migrationInsert()`.
2. **Runner** – walks a directory or file and invokes the driver for each
   item.  The default `FileRunner` knows how to process `.sql` and `.php`
   files.

Basic example using PDO (MySQL/MariaDB, PostgreSQL, SQLite, etc.):

```php
use anovsiradj\sqlrun\drivers\PdoDriver;
use anovsiradj\sqlrun\runners\FileRunner;

require __DIR__ . '/vendor/autoload.php';

$pdo = new \PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$driver = new PdoDriver;
$driver->connect($pdo);
$driver->migration = true;          // track executed migrations

// optional: attach a PSR‑3 logger (monolog, framework logger, etc.)
$driver->setLogger(new \Psr\Log\NullLogger());

$runner = new FileRunner;
$runner->driver($driver);

// execute a pair of directories: structures, then patches, then contents
$runner->run("/path/to/structures.sql");
$runner->runDir("/path/to/patches");
$runner->run("/path/to/contents.sql");
```

You can also use the `Yii2Driver` and `LaravelDriver` if you are running
inside those frameworks; they will pick up the connection from the
application by default.  See `tests/*.php` for tiny example scripts.

#### `.sql` vs `.php` files

* `.sql` files are read and executed directly by the driver.  Use them for
  static statements such as `CREATE TABLE`, `ALTER TABLE`, `INSERT`, etc.
* `.php` files are included inside a closure that has `$driver` and `$runner`
  in scope; they may return `false` to signal failure or any other value to
  indicate success.  This allows you to write conditional or dynamic
  migrations (`if (! $driver->fetchOne(...)) { ... }`).

#### migration tracking

When `$driver->migration` is `true` the runner will query `migrationExist()`
before running each file and call `migrationInsert()` on success.  The PDO
driver expects a table named `migrations` with at least a string column
`migration`; you can create it yourself or extend the driver to use a
different table.

### contributor guidelines

Contributions are very welcome!  Please follow these steps:

1. **Fork the repository** and create a feature branch for your work.
2. **Run the tests** (when available) and add new tests for any bug fix or
   new feature.  PHPUnit is used for automated testing – see the
   `tests/` directory for examples of the current manual scripts.
3. Follow **PSR‑12 coding style** and run `php-cs-fixer` or `phpcs` prior to
   submitting a PR.
4. **Update this README** with any new configuration, usage or behaviour
   changes.
5. Open a pull request against `main` and describe your changes clearly.
   The maintainers will review and merge when appropriate.

For major changes (API-breaking, PHP version bump, new drivers) please open
an issue first so we can discuss the design.

Bug reports and feature requests are tracked via GitHub issues; please
include enough detail to reproduce the problem.

---

### todos (grouped and ordered by priority)

**MUST:**

**MAY:**
- pdo sqlite driver
- sqlite driver
- sqlsrv driver
- pdo firebird driver

**DONE:**
- has loader to load and read files
- can using `.sql` and `.php`
- pdo mysql/mariadb driver
- pdo pgsql driver
- yii2 driver
- laravel driver (migration)
