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

use Drewlabs\Overloadable\NamedArgument as Argument;
use Drewlabs\Overloadable\DataTypes;
use Drewlabs\Overloadable\Tests\Stubs\ConsoleLogger;
use Drewlabs\Overloadable\Tests\Stubs\Logger;
use Drewlabs\Overloadable\Tests\Stubs\TestClass;
use Drewlabs\Overloadable\Tests\TestCase;
use stdClass;

class NamedArgumentTest extends TestCase
{

    public function test_named_argument_is_optional_return_true_if_argument_is_marked_as_optional()
    {
        $arg = new Argument('myArg', DataTypes::ANY, true);
        $this->assertTrue($arg->isOptional());
    }

    public function test_named_argument_get_type_return_provided_type_value()
    {
        $arg = new Argument('myArg', 'string', true);
        $this->assertEquals('string', $arg->getType());
    }


    public function test_named_argument_match_return_false_if_scalar_type_does_not_match()
    {
        $arg = new Argument('myArg', 'int', false);
        $this->assertFalse($arg->match('Hello World'));
        $arg = new Argument('myArg', 'string', false);
        $this->assertFalse($arg->match(4.5));
    }

    
    public function test_named_argument_match_return_true_if_scalar_type_does_match()
    {
        $arg = new Argument('myArg', 'string', false);
        $this->assertTrue($arg->match('Hello World'));
        $arg = new Argument('myArg', 'double', false);
        $this->assertTrue($arg->match(4.5));
    }

    public function test_named_argument_match_return_false_if_object_type_does_not_match()
    {
        $arg = new Argument('myArg', 'string');
        $this->assertFalse($arg->match(new stdClass));
        $this->assertFalse($arg->match(new ConsoleLogger));

        $arg2 = new Argument('myArg', TestClass::class);
        $this->assertFalse($arg2->match(new ConsoleLogger));
    }

    
    public function test_named_argument_match_return_true_if_object_type_does_match()
    {
        $arg = new Argument('myArg', 'object');
        $this->assertTrue($arg->match(new stdClass));
        $this->assertTrue($arg->match(new ConsoleLogger));

        $arg2 = new Argument('myArg', Logger::class);
        $this->assertTrue($arg2->match(new ConsoleLogger));
    }

    public function test_named_argument_match_return_true_if_argument_is_optional_and_provided_value_equals_null()
    {
        $arg = new Argument('myArg', 'string', true);
        $this->assertTrue($arg->match(null));
    }
}