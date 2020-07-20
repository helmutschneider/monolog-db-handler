<?php
declare(strict_types = 1);

namespace HelmutSchneider\Tests\Monolog;

use Exception;
use HelmutSchneider\Monolog\DetailedNormalizerFormatter;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class DetailedNormalizeFormatterTest
 * @package HelmutSchneider\Tests\Monolog
 */
class DetailedNormalizerFormatterTest extends TestCase
{
    public DetailedNormalizerFormatter $formatter;

    public function setUp(): void
    {
        parent::setUp();

        $this->formatter = new DetailedNormalizerFormatter();
    }

    public function testIncludesPublicProperties()
    {
        $e = new class extends \Exception {
            public string $yee = 'boi';
        };

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertSame('boi', $data['exception']['yee']);
    }

    public function testIncludesTraceArguments()
    {
        $message = base64_encode(random_bytes(128));
        $a = function (string $message) {
            return new \Exception('YEE');
        };
        $e = $a($message);

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertSame($message, $data['exception']['trace'][0]['args'][0]);
        $this->assertSame(__CLASS__, $data['exception']['trace'][1]['class']);
        $this->assertSame(__FUNCTION__, $data['exception']['trace'][1]['function']);
    }

    public function testWorksWithObjectAsArgument()
    {
        $a = function ($arg) {
            return new \Exception('YEE');
        };
        $b = function ($arg) use ($a) {
            return $a($arg);
        };
        $c = function ($arg) use ($b) {
            return $b($arg);
        };
        $e = $c((object) ['yee' => 'boi']);

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertSame('[object] (stdClass: {"yee":"boi"})', $data['exception']['trace'][0]['args'][0]);
        $this->assertMatchesRegularExpression('/\[object\] \(stdClass: [a-f0-9]+\)/', $data['exception']['trace'][1]['args'][0]);
    }

    public function testPreventsRecursionInExceptionProperties()
    {
        $e = new class extends Exception {
            public $yee;
            public function __construct($message = "", $code = 0, Throwable $previous = null)
            {
                parent::__construct($message, $code, $previous);

                $this->yee = [$this];
            }
        };

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertSame(true, true);
    }
}
