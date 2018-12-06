<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-12-06
 * Time: 18:29
 */
declare(strict_types = 1);

namespace HelmutSchneider\Monolog;


use Exception;
use ReflectionClass;
use ReflectionProperty;
use Throwable;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Extended version of Monolog\Formatter\NormalizerFormatter
 * that provides a detailed, complete stack trace. Also includes
 * public properties of the logged exception.
 *
 * Class DetailedNormalizeFormatter
 * @package HelmutSchneider\Monolog
 */
class DetailedNormalizeFormatter extends NormalizerFormatter
{

    /**
     * @param Throwable|Exception $e
     * @return array
     * @throws \ReflectionException
     */
    protected function normalizeException($e)
    {
        if (!$e instanceof Exception && !$e instanceof Throwable) {
            throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($e).' / '.get_class($e));
        }

        $data = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
            'trace' => $this->normalize($e->getTrace()),
        );

        $reflection = new ReflectionClass($e);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $data[$property->getName()] = $this->normalize(
                $property->getValue($e)
            );
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous);
        }

        return $data;
    }

}