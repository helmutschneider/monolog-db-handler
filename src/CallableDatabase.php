<?php
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
     * @var boolean
     */
    protected bool $isInvokingDatabase = false;

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
        if ($this->isInvokingDatabase) {
            return null;
        }
        $this->isInvokingDatabase = true;
        $result = call_user_func($this->fn, $query, $parameters);
        $this->isInvokingDatabase = false;
        return $result;
    }
}
