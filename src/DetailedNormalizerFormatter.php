<?php
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
class DetailedNormalizerFormatter extends NormalizerFormatter
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

        $seen = [];
        $data = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
            'trace' => $this->normalizeAndRemoveDuplicates($e->getTrace(), $seen),
        ];

        $reflection = new ReflectionClass($e);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            if (!isset($data[$name])) {
                $data[$name] = $this->normalize(
                    $property->getValue($e)
                );
            }
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous);
        }

        return $data;
    }

    /**
     * Stack traces generally contain lots of references to the same object.
     * To prevent memory exhaustion during serialization this function is used
     * to remove duplicates from the trace.
     *
     * @param mixed $data
     * @param array $seenObjects
     * @return mixed
     */
    protected function normalizeAndRemoveDuplicates($data, array &$seenObjects)
    {
        $out = $data;
        if (is_array($out)) {
            foreach ($out as $key => $value) {
                $out[$key] = $this->normalizeAndRemoveDuplicates($value, $seenObjects);
            }
        } else if (is_object($out)) {
            if (in_array($out, $seenObjects)) {
                return sprintf("[object] (%s: %s)", get_class($out), spl_object_hash($out));
            }

            $seenObjects[] = $data;
        }

        return $this->normalize($out);
    }
}
