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

use Drewlabs\Overloadable\IntersectionTypeArgument as Argument;
use Drewlabs\Overloadable\Tests\Stubs\Console;
use Drewlabs\Overloadable\Tests\Stubs\ConsoleLogger;
use Drewlabs\Overloadable\Tests\Stubs\FileLogger;
use Drewlabs\Overloadable\Tests\Stubs\Logger;
use Drewlabs\Overloadable\Tests\TestCase;
use stdClass;

class IntersectionTypeArgumentTest extends TestCase
{

    public function method(FileLogger&ConsoleLogger $p)
    {
    }

    public function writeLog(Console&Logger $c)
    {
    }

    private function getTestType(string $method = 'method'): \ReflectionIntersectionType
    {
        $method = new \ReflectionMethod($this, $method);
        return $method->getParameters()[0]->getType();
    }



    public function test_intersection_type_argument_is_optional_return_true_if_argument_is_marked_as_optional()
    {
        $arg = new Argument($this->getTestType(), null, true);
        $this->assertTrue($arg->isOptional());
    }

    public function test_intersection_type_argument_get_type_return_provided_type_value()
    {
        $arg = new Argument($this->getTestType(), null, true);
        $this->assertEquals(sprintf('%s&%s', FileLogger::class, ConsoleLogger::class), $arg->getType());
    }

    public function test_intersection_type_argument_match_return_false_if_object_type_does_not_match()
    {
        $object = new stdClass;
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertFalse($arg->match($object));
        $this->assertFalse($arg->match(new FileLogger));
    }


    public function test_intersection_type_argument_match_return_true_if_object_type_does_match()
    {
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertTrue($arg->match(new ConsoleLogger));
    }

    public function test_intersection_type_argument_match_return_true_if_argument_is_optional_and_provided_value_equals_null()
    {
        $arg = new Argument($this->getTestType('writeLog'), null, true);
        $this->assertTrue($arg->match(null));
    }

}
