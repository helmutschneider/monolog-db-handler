<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2018-12-06
 * Time: 18:35
 */
declare(strict_types = 1);

namespace HelmutSchneider\Tests\Monolog;

use HelmutSchneider\Monolog\DetailedNormalizeFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Class DetailedNormalizeFormatterTest
 * @package HelmutSchneider\Tests\Monolog
 */
class DetailedNormalizeFormatterTest extends TestCase
{

    /**
     * @var DetailedNormalizeFormatter
     */
    public $formatter;

    public function setUp()
    {
        parent::setUp();

        $this->formatter = new DetailedNormalizeFormatter();
    }

    public function testIncludesPublicProperties()
    {
        $e = new class extends \Exception {
            public $yee = 'boi';
        };

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertEquals('boi', $data['exception']['yee']);
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

        $this->assertEquals($message, $data['exception']['trace'][0]['args'][0]);
        $this->assertEquals(__CLASS__, $data['exception']['trace'][1]['class']);
        $this->assertEquals(__FUNCTION__, $data['exception']['trace'][1]['function']);
    }

    public function testWorksWithObjectAsArgument()
    {
        $a = function ($arg) {
            return new \Exception('YEE');
        };
        $e = $a((object) ['yee' => 'boi']);

        $data = $this->formatter->format([
            'exception' => $e,
        ]);

        $this->assertStringStartsWith('[object]', $data['exception']['trace'][0]['args'][0]);
    }

}
