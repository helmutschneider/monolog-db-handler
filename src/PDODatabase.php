<?php
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

use PDO;

class PDODatabase implements DatabaseInterface
{
    /**
     * @var PDO
     */
    protected PDO $pdo;

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
