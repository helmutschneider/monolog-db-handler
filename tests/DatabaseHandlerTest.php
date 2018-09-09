<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-09-09
 * Time: 16:15
 */
declare(strict_types = 1);

namespace HelmutSchneider\Tests\Monolog;

use HelmutSchneider\Monolog\DatabaseHandler;
use PDO;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DatabaseHandlerTest extends TestCase
{

    /**
     * @return array
     */
    public function databaseProvider(): array
    {
        $configs = require __DIR__ . '/config.php';

        return array_map(function ($config) {
            $db = new PDO($config['dsn'], $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $db->exec(file_get_contents($config['schema']));
            return [$db];
        }, $configs);
    }

    /**
     * @dataProvider databaseProvider
     * @param PDO $db
     */
    public function testWritesToDatabase(PDO $db)
    {
        $logger = new Logger('test', [
            new DatabaseHandler($db, 'log'),
        ]);

        $logger->log(Logger::DEBUG, 'Hello World', [
            'some_var' => 1,
        ]);

        $stmt = $db->prepare('SELECT * FROM log');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('test', $rows[0]['channel']);
        $this->assertEquals(Logger::DEBUG, $rows[0]['level']);
        $this->assertEquals('Hello World', $rows[0]['message']);
        $this->assertEquals('{"some_var":1}', $rows[0]['context']);
    }

}