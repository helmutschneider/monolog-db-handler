# Database handler for Monolog
Simple handler to write Monolog entries to a PDO-database.

## Usage
```php
use PDO;
use Monolog\Logger;
use HelmutSchneider\Monolog\DatabaseHandler;

$db = new PDO(...);
$logger = new Logger('channel', [
    // second parameter is an optional table name. defaults to "log"
    new DatabaseHandler($db, 'log'),
]);

$logger->log(Logger::DEBUG, 'Hello World');

```

## Testing
Copy `tests/config.sample.php` to `tests/config.php`. You may comment out database entries that you don't want to test.
Then execute:
```shell
vendor/bin/phpunit
```
