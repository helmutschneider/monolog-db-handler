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
     * @var mixed[]
     */
    protected array $seenObjects = [];

    /**
     * @inheritDoc
     */
    public function format(array $record)
    {
        $this->seenObjects = [];

        return parent::format($record);
    }

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

        if (!in_array($e, $this->seenObjects)) {
            $this->seenObjects[] = $e;
        }

        $data = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
            'trace' => $this->normalizeAndRemoveDuplicates($e->getTrace()),
        ];

        $reflection = new ReflectionClass($e);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            if (!isset($data[$name])) {
                $data[$name] = $this->normalizeAndRemoveDuplicates(
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
     * @return mixed
     */
    protected function normalizeAndRemoveDuplicates($data)
    {
        $out = $data;
        if (is_array($out)) {
            foreach ($out as $key => $value) {
                $out[$key] = $this->normalizeAndRemoveDuplicates($value);
            }
        } else if (is_object($out)) {
            if (in_array($out, $this->seenObjects)) {
                return sprintf("[object] (%s: %s)", get_class($out), spl_object_hash($out));
            }

            $this->seenObjects[] = $data;
        }

        return $this->normalize($out);
    }
}
