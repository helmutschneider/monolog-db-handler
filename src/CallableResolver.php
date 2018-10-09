<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-10-09
 * Time: 19:00
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

use PDO;

class CallableResolver implements PDOResolver
{

    /**
     * @var callable
     */
    protected $fn;

    /**
     * @var array
     */
    protected $args;

    /**
     * CallableResolver constructor.
     * @param callable $fn
     * @param array $args
     */
    function __construct(callable $fn, array $args = [])
    {
        $this->fn = $fn;
        $this->args = $args;
    }

    /**
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return call_user_func_array($this->fn, $this->args);
    }

}
