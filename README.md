# Database handler for Monolog
Simple handler to write Monolog entries to a PDO-database.

## Usage
Database schemas for MySQL and SQLite can be found in `schema/`.
When your database is ready you can inject the handler into your logger.

```php
use PDO;
use Monolog\Logger;
use HelmutSchneider\Monolog\DatabaseHandler;
use HelmutSchneider\Monolog\CallableResolver;

$db = new PDO(...);
$resolver = new CallableResolver(function () use ($db) {
    return $db;
});
$logger = new Logger('channel', [
    // second parameter is an optional table name. defaults to "log"
    new DatabaseHandler($resolver, 'log'),
]);

$logger->log(Logger::DEBUG, 'Hello World');

```

## Testing
Copy `tests/config.sample.php` to `tests/config.php`. You may comment out database entries that you don't want to test.
Then execute:
```shell
vendor/bin/phpunit
```
