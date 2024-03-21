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

use Drewlabs\Overloadable\UnionTypeArgument as Argument;
use Drewlabs\Overloadable\Tests\Stubs\Console;
use Drewlabs\Overloadable\Tests\Stubs\ConsoleLogger;
use Drewlabs\Overloadable\Tests\Stubs\FileLogger;
use Drewlabs\Overloadable\Tests\Stubs\Logger;
use Drewlabs\Overloadable\Tests\TestCase;
use stdClass;

class UnionTypeArgumentTest extends TestCase
{

    public function method(string|(FileLogger&ConsoleLogger) $p)
    {
    }

    public function writeLog(string|(Console&Logger) $c)
    {
    }

    public function log(string|Console|Logger $c)
    {
    }

    private function getTestType(string $method = 'method'): \ReflectionUnionType
    {
        $method = new \ReflectionMethod($this, $method);
        return $method->getParameters()[0]->getType();
    }



    public function test_union_type_argument_is_optional_return_true_if_argument_is_marked_as_optional()
    {
        $arg = new Argument($this->getTestType(), null, true);
        $this->assertTrue($arg->isOptional());
    }

    public function test_union_type_argument_get_type_return_provided_type_value()
    {
        $arg = new Argument($this->getTestType(), null, true);
        $this->assertEquals(sprintf('(%s&%s)|string', FileLogger::class, ConsoleLogger::class), $arg->getType());
    }

    public function test_union_type_argument_match_return_false_if_object_type_does_not_match()
    {
        $object = new stdClass;
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertFalse($arg->match($object));
    }


    public function test_union_type_argument_match_return_true_if_object_type_does_match()
    {
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertTrue($arg->match(new ConsoleLogger));
        $this->assertFalse($arg->match(new FileLogger));
    }

    public function test_union_type_argument_match_return_true_if_object_type_does_match_for_all_union()
    {
        $arg = new Argument($this->getTestType('log'), null, true);
        $this->assertTrue($arg->match(new ConsoleLogger));
        $this->assertTrue($arg->match(new FileLogger));
        $this->assertTrue($arg->match('Hello World'));
    }

    public function test_union_type_argument_match_return_true_if_argument_is_optional_and_provided_value_equals_null()
    {
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertTrue($arg->match(null));
    }

}
