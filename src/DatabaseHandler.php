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
use Monolog\Formatter\NormalizerFormatter;
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
        INSERT INTO `%s`(`channel`, `level`, `datetime`, `message`, `context`, `extra`)
             VALUES (:channel, :level, :datetime, :message, :context, :extra)
SQL;

    protected const JSON_ENCODE_FLAGS =
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE;

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

        $query = sprintf(static::INSERT_QUERY, $this->tableName);
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':channel' => $record['formatted']['channel'],
            ':level' => $record['formatted']['level'],
            ':datetime' => $record['formatted']['datetime'],
            ':message' => $record['formatted']['message'],
            ':context' => json_encode($record['formatted']['context'], static::JSON_ENCODE_FLAGS),
            ':extra' => json_encode($record['formatted']['extra'], static::JSON_ENCODE_FLAGS),
        ]);

        $this->isHandling = false;
    }

    /**
     * @return \Monolog\Formatter\FormatterInterface|NormalizerFormatter
     */
    public function getFormatter()
    {
        return new NormalizerFormatter();
    }

}
