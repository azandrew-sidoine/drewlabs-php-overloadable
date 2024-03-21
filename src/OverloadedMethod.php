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

namespace Drewlabs\Overloadable;

use BadMethodCallException;
use Closure;
use Drewlabs\Overloadable\Concerns\HasArguments;
use Drewlabs\Overloadable\Argument;
use Exception;

class OverloadedMethod
{
    use HasArguments;

    /**
     * A callable object.
     *
     * @var \Closure
     */
    private $callable;

    /**
     * Method can be use as fallback.
     *
     * @var bool
     */
    private $fallback = false;

    /**
     * Creates class instance
     * 
     * @param mixed $overload 
     * @param mixed $method 
     * @param mixed $object 
     * @return void 
     * @throws Exception 
     */
    public function __construct($overload, $method, $object)
    {
        if (!\is_int($method)) {
            return $this->useSignature($overload, $method, $object);
        } 
        if ($overload instanceof \Closure) {
            return $this->useClosureReflection($overload);
        }
        if (\is_string($overload) && method_exists($object, $overload)) {
            return $this->useMethodReflection($object, $overload);
        }
        throw new \Exception('Unknown overloaded method definition.');
    }

    public function matches(array $args = [])
    {
        // TODO: Use buffering to easily resolve matches in future
        if (empty($this->arguments->getAll())) {
            // Makes methods that accepts zero argument, as fallback
            $this->fallback = true;
            // The empty argument method matches if and only if the list of passed argument is empty
            return empty($args);
        }
        $value_args_count = \count($args);
        if ((0 === $this->arguments->optionalArgumentsCount()) && ($arguments = $this->arguments->getAll())) {
            return $value_args_count === \count($arguments) && $this->argsMatch($arguments, $args);
        }
        // Get all the arguments
        $arguments = $this->arguments->getAll();
        $total_argument_count = \count($arguments);
        if ($value_args_count > $this->arguments->requiredArgumentsCount()) {
            $arguments = $value_args_count > $total_argument_count ? \array_slice($arguments, 0, $total_argument_count) : \array_slice($arguments, 0, $value_args_count);
        }
        return $this->argsMatch($arguments, $args);
    }

    /**
     * If the current method is to be used as fallback
     * 
     * @return bool 
     */
    public function isFallback()
    {
        return $this->fallback;
    }

    /**
     * Call or invoke the overloaded method
     * 
     * @param mixed $args 
     * @return mixed 
     * @throws BadMethodCallException 
     */
    public function call($args)
    {
        if (!($this->callable instanceof \Closure)) {
            throw new BadMethodCallException();
        }
        return $this->callable->__invoke(...$args);
    }

    /**
     * Instanciate the current instance using array signature
     * 
     * @param mixed $signature 
     * @param mixed $method 
     * @param mixed $object 
     * @return void 
     */
    private function useSignature($signature, $method, $object)
    {
        $this->callable = $this->bindCallable($object, $method);
        $this->arguments = OverloadMethodArguments::fromArray($signature);
    }

    /**
     * Instanciate current instance through method reflection
     * 
     * @param object $object 
     * @param string $method
     * 
     * @return void 
     */
    private function useMethodReflection($object, string $method)
    {
        $this->callable = $this->bindCallable($object, $method);
        $reflected = new \ReflectionMethod($object, $method);
        // Added set accessible to make the method callable using invoke
        // even though the method were declared protected or private
        $reflected->setAccessible(true);
        $this->arguments = OverloadMethodArguments::fromReflection($reflected);
    }

    /**
     * Instanciate current instance through closure reflection
     * 
     * @param Closure $closure
     * 
     * @return void 
     */
    private function useClosureReflection(\Closure $closure)
    {
        $this->callable = $closure;
        $reflected = new \ReflectionFunction($closure);
        $this->arguments = OverloadMethodArguments::fromReflection($reflected);
    }

    /**
     * Bind callable to object instance to avoid bad method call exception
     * 
     * @param object $object 
     * @param string $method 
     * @return Closure|false 
     */
    private function bindCallable($object, string $method)
    {
        $closure = function (...$args) use ($method) {
            return $this->{$method}(...$args);
        };
        return $closure->bindTo($object, $object);
    }

    /**
     * Arguments matcher function
     * 
     * @param Argument[] $arguments 
     * @param array $params 
     * @return bool 
     */
    private function argsMatch(array $arguments, array $params)
    {
        return array_reduce(
            TypesUtil::zip($params, $arguments),
            static function ($isMatch, $argAndType) {
                /**
                 * @var Argument|NamedArgument|IntersectionTypeArgument|UnionTypeArgument $type
                 */
                [$arg, $type] = $argAndType;
                if (null === $type) {
                    return null === $arg ? $isMatch && true : $isMatch && false;
                }
                return $isMatch && $type->match($arg);
            },
            true
        );
    }
}
