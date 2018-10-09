<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-10-10
 * Time: 00:31
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;

/**
 * Class CallableDatabase
 * @package HelmutSchneider\Monolog
 */
class CallableDatabase implements DatabaseInterface
{

    /**
     * @var callable
     */
    protected $fn;

    /**
     * CallableDatabase constructor.
     * @param callable $fn
     */
    function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return mixed
     */
    public function execute(string $query, array $parameters = [])
    {
        return call_user_func($this->fn, $query, $parameters);
    }

}
