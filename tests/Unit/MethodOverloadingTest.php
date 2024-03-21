<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Overloadable\Tests\Unit;

use Drewlabs\Overloadable\MethodCallExpection;
use Drewlabs\Overloadable\Tests\Stubs\ConsoleLogger;
use Drewlabs\Overloadable\Tests\Stubs\FileLogger;
use Drewlabs\Overloadable\Tests\Stubs\MethodOverloadClass;
use Drewlabs\Overloadable\Tests\Stubs\TestClass;
use Drewlabs\Overloadable\Tests\TestCase;

class MethodOverloadingTest extends TestCase
{
    public function test_class_method_overload()
    {
        $test = new TestClass();
        $this->assertSame('Logging to the console...', $test->log(new ConsoleLogger), 'Expect ConsoleLogger::log to be called');
    }

    public function test_file_logger_called_if_file_logger_instance_is_provided()
    {
        $test = new TestClass();
        $this->assertSame('ERROR024: Logging to the system resource...', $test->log(new FileLogger), 'Expect FileLogger::log to be called');
        $this->assertSame('ERROR500: Logging to the system resource...', $test->log(new FileLogger, 'ERROR500'), 'Expect FileLogger::log to be called');
    }

    public function test_overload_throws_exception_for_missing_overload()
    {
        $this->expectException(MethodCallExpection::class);
        $test = new TestClass();
        $this->assertSame('ERROR024: Logging to the system resource...', $test->log(new FileLogger, []), 'Expect FileLogger::log to be called');

    }

    public function test_inline_overload()
    {
        $this->assertTrue('METHOD B' === (new MethodOverloadClass())->someMethod(new \DateTime(), [], 20), 'Expect the return value of MethodOverloadClass::someMethod to return METHOD B');
        $this->assertTrue('METHOD C' === (new MethodOverloadClass())->someMethod([], []), 'Expect the return value of MethodOverloadClass::someMethod to return METHOD C');
    }


    public function test_intersection_and_union_type_overload()
    {
        $fLogger = new FileLogger;
        $cLogger = new ConsoleLogger;
        $instance = new TestClass;

        $this->assertEquals("fLogger!: Logging to the system resource...", $instance->writeLog($fLogger, "fLogger!"));
        $this->assertEquals("Writing to console: cLogger", $instance->writeLog($cLogger, "cLogger"));
    }
}
