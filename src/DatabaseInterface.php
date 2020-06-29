<?php
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

/**
 * Interface DatabaseInterface
 * @package HelmutSchneider\Monolog
 */
interface DatabaseInterface
{
    /**
     * @param string $query
     * @param array $parameters
     * @return mixed
     */
    public function execute(string $query, array $parameters = []);
}
