<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-10-10
 * Time: 00:12
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

use PDO;

class PDODatabase implements DatabaseInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * PDODatabase constructor.
     * @param PDO $pdo
     */
    function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return mixed
     */
    public function execute(string $query, array $parameters = [])
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($parameters);
    }
}
