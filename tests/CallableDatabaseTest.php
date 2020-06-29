<?php
declare(strict_types=1);

namespace HelmutSchneider\Tests\Monolog;


use HelmutSchneider\Monolog\CallableDatabase;
use PHPUnit\Framework\TestCase;

class CallableDatabaseTest extends TestCase
{
    public function testPreventsRecursion()
    {
        $handler = new CallableDatabase(function ($query, $parameters) use (&$handler) {
            return $handler->execute($query, $parameters);
        });

        $handler->execute('', []);

        $this->assertSame(true, true);
    }
}
