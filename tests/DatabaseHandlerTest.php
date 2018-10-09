<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-09-09
 * Time: 16:15
 */
declare(strict_types = 1);

namespace HelmutSchneider\Tests\Monolog;

use HelmutSchneider\Monolog\CallableResolver;
use HelmutSchneider\Monolog\DatabaseHandler;
use HelmutSchneider\Monolog\PDOResolver;
use PDO;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DatabaseHandlerTest extends TestCase
{

    /**
     * @var PDO[]
     */
    public static $dbs = [];

    public function setUp()
    {
        parent::setUp();

        foreach (static::$dbs as $db) {
            $db->exec('DELETE FROM log');
        }
    }

    /**
     * @return array
     */
    public static function databaseProvider(): array
    {
        if (empty(static::$dbs)) {
            $configs = require __DIR__ . '/config.php';
            static::$dbs = array_map(function ($config) {
                $db = new PDO($config['dsn'], $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $db->exec(file_get_contents($config['schema']));
                return $db;
            }, $configs);
        }

        return array_map(function (PDO $db) {
            return [
                new CallableResolver(function () use ($db) {
                    return $db;
                }),
            ];
        }, static::$dbs);
    }

    /**
     * @dataProvider databaseProvider
     * @param PDOResolver $resolver
     */
    public function testWritesToDatabase(PDOResolver $resolver)
    {
        $logger = new Logger('test', [
            new DatabaseHandler($resolver, 'log'),
        ]);

        $logger->log(Logger::DEBUG, 'Hello World', [
            'some_var' => 1,
        ]);

        $stmt = $resolver->getPDO()->prepare('SELECT * FROM log');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('test', $rows[0]['channel']);
        $this->assertEquals(Logger::DEBUG, $rows[0]['level']);
        $this->assertEquals('Hello World', $rows[0]['message']);
        $this->assertEquals('{"some_var":1}', $rows[0]['context']);
    }

    /**
     * @dataProvider databaseProvider
     * @param PDOResolver $resolver
     */
    public function testSerializesComplexObjects(PDOResolver $resolver)
    {
        $logger = new Logger('test', [
            new DatabaseHandler($resolver, 'log'),
        ]);

        $logger->log(Logger::DEBUG, 'Complex data', [
            'handle' => STDIN,
        ]);

        $stmt = $resolver->getPDO()->prepare('SELECT * FROM log');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertContains('"handle":', $rows[0]['context']);
    }

}