<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-09-09
 * Time: 15:46
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

use DateTimeInterface;
use PDO;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class DatabaseHandler
 * @package HelmutSchneider\Monolog
 */
class DatabaseHandler extends AbstractProcessingHandler
{

    protected const INSERT_QUERY = <<<SQL
        INSERT INTO `%s`(`channel`, `level`, `datetime`, `message`)
             VALUES (:channel, :level, :datetime, :message)
SQL;

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var bool
     */
    protected $isHandling = false;

    /**
     * DatabaseHandler constructor.
     * @param PDO $db
     * @param string $tableName
     * @param int $level
     * @param bool $bubble
     */
    function __construct(PDO $db, string $tableName = 'log', int $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->db = $db;
        $this->tableName = $tableName;

        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        // if this condition is true an exception was thrown
        // while inserting a previous error. to prevent an
        // infinite try-catch logger loop, break here.
        if ($this->isHandling) {
            return;
        }

        $this->isHandling = true;

        /* @var DateTimeInterface $dt */
        $dt = $record['datetime'];

        $query = sprintf(static::INSERT_QUERY, $this->tableName);
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':channel' => $record['channel'],
            ':level' => $record['level'],
            ':datetime' => $dt->format('Y-m-d H:i:s'),
            ':message' => $record['formatted'],
        ]);

        $this->isHandling = false;
    }

}
