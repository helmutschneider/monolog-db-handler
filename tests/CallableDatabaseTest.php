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

    public function testShouldNotBreakIfTheCallableThrows()
    {
        $handler = new CallableDatabase(function ($query, $parameters) {
            if ($query === 'THROW_ME_PLEASE') {
                throw new \RuntimeException('Yee');
            }
            return $query;
        });

        try {
            $handler->execute('THROW_ME_PLEASE');
        } catch (\Exception $e) {
        }

        $this->assertSame('OK', $handler->execute('OK'));
    }
}
