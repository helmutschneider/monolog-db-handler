<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-10-09
 * Time: 18:57
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

use PDO;

interface PDOResolver
{

    /**
     * @return PDO
     */
    public function getPDO(): PDO;

}
