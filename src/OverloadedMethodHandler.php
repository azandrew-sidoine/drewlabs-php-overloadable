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
use Drewlabs\Core\Helpers\Arr;
use Drewlabs\Overloadable\Lang\DataTypes;
use Drewlabs\Overloadable\Lang\Argument;
use Drewlabs\Overloadable\Lang\ArgumentType;
use Drewlabs\Overloadable\Lang\NamedArgument;
use ReflectionIntersectionType;
use ReflectionUnionType;

class OverloadedMethodHandler
{
    /**
     * A callable object.
     *
     * @var \Closure
     */
    private $callable;

    /**
     * List of argument passed to the method.
     *
     * @var OverloadMethodArgumentsContainer
     */
    private $arguments = [];

    /**
     * Method can be use as fallback.
     *
     * @var bool
     */
    private $isFallback_ = false;

    public function __construct($signature, $method, $object)
    {
        if (!\is_int($method)) {
            $this->buildFromSignature($signature, $method, $object);
        } elseif (\is_string($signature) && method_exists($object, $signature)) {
            $this->buildUsingMethodReflection($object, $signature);
        } elseif ($signature instanceof \Closure) {
            $this->buildUsingClosureReflection($signature);
        } else {
            throw new \Exception('Unrecognized overloaded method definition.');
        }
    }

    public function matches(array $args = [])
    {
        if (empty($this->arguments->getAll())) {
            // Makes methods that accepts zero argument, as fallback
            $this->isFallback_ = true;
            // The empty argument method matches if and only if the list of passed argument is empty
            return empty($args);
        }
        $value_args_count = \count($args);
        if ((0 === $this->arguments->optionalArgumentsCount()) && ($arguments_ = $this->arguments->getAll())) {
            return $value_args_count === \count($arguments_) &&
                $this->matchArgumentsToParameters($arguments_, $args);
        }
        // Get all the arguments
        $arguments_ = $this->arguments->getAll();
        $total_argument_count = \count($arguments_);
        if ($value_args_count > $this->arguments->requiredArgumentsCount()) {
            $arguments_ = $value_args_count > $total_argument_count ?
                \array_slice($arguments_, 0, $total_argument_count) :
                \array_slice($arguments_, 0, $value_args_count);
        }

        return $this->matchArgumentsToParameters($arguments_, $args);
    }

    public function isFallback()
    {
        return $this->isFallback_;
    }

    /**
     * {@inheritDoc}
     */
    public function getArguments()
    {
        return array_values(
            array_map(
                static function ($arg) {
                    return (string) $arg;
                },
                $this->arguments->getAll()
            )
        );
    }

    public function getOptionalArguments()
    {
        return array_values(
            array_map(
                static function ($arg) {
                    return (string) $arg;
                },
                $this->arguments->getOptionalArguments() ?? []
            )
        );
    }

    public function getRequiredArguments()
    {
        return array_values(
            array_map(
                static function ($arg) {
                    return (string) $arg;
                },
                $this->arguments->getRequiredArguments() ?? []
            )
        );
    }

    public function call($args)
    {
        if (!($this->callable instanceof \Closure)) {
            throw new BadMethodCallException();
        }

        return $this->callable->__invoke(...$args);
    }

    private function buildFromSignature($signature, $method, $object)
    {
        $this->callable = $this->bindCallable($object, $method);
        $this->arguments = $this->mapArraySignature($signature);
    }

    private function buildUsingMethodReflection($object, $method)
    {
        $this->callable = $this->bindCallable($object, $method);
        $reflected = new \ReflectionMethod($object, $method);
        // Added set accessible to make the method callable using invoke
        // even though the method were declared protected or private
        $reflected->setAccessible(true);
        $this->arguments = $this->mapArguments($reflected);
    }

    private function bindCallable($object, $method)
    {
        $closure = function (...$args) use ($method) {
            return $this->{$method}(...$args);
        };

        return $closure->bindTo($object, $object);
    }

    private function buildUsingClosureReflection(\Closure $closure)
    {
        $this->callable = $closure;
        $reflected = new \ReflectionFunction($closure);
        $this->arguments = $this->mapArguments($reflected);
    }

    /**
     * @param \ReflectionFunctionAbstract $reflectionFunction
     *
     * @return OverloadMethodArgumentsContainer
     */
    private function mapArguments($reflectionFunction)
    {
        // TODO : Initialize values
        $optinal_args_count = 0;
        $required_args_count = 0;
        $optional_arguments = [];
        $required_arguments = [];
        // #endregion Initialize values
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $arg = new NamedArgument(
                $parameter->getName(),
                (!$parameter->hasType() ||
                    (class_exists(ReflectionUnionType::class) && $parameter->getType() instanceof ReflectionUnionType) ||
                    (class_exists(ReflectionIntersectionType::class) && $parameter->getType() instanceof ReflectionIntersectionType)) ?
                    DataTypes::ANY : $parameter->getType()->getName(),
                $parameter->isOptional() ? ArgumentType::OPTIONAL : ArgumentType::REQUIRED
            );
            if ($parameter->isOptional()) {
                ++$optinal_args_count;
                $optional_arguments[] = $arg;
            }
            if (!$parameter->isOptional()) {
                ++$required_args_count;
                $required_arguments[] = $arg;
            }
        }

        return new OverloadMethodArgumentsContainer(
            $this->normalizeTypes([...$required_arguments, ...$optional_arguments]),
            $required_arguments,
            $optional_arguments,
            $required_args_count,
            $optinal_args_count,
        );
    }

    /**
     * @return OverloadMethodArgumentsContainer
     */
    private function mapArraySignature(array $signature = [])
    {
        // TODO : Initialize values
        $optinal_args_count = 0;
        $required_args_count = 0;
        $required_arguments = [];
        $optional_arguments = [];
        // #endregion Initialize values
        foreach ($signature as $curr) {
            $type = DataTypes::ANY;
            $state = ArgumentType::REQUIRED;
            if (\is_string($curr)) {
                // Argument is required
                $type = $curr;
            } elseif (\is_array($curr)) {
                $type = DataTypes::ANY;
                $total_items = \count($curr);
                if ($total_items > 0) {
                    $type = $curr[0] ?? DataTypes::ANY;
                    if (($total_items > 1 ? ($curr[1] ?? ArgumentType::OPTIONAL) : ArgumentType::REQUIRED) === ArgumentType::OPTIONAL) {
                        $state = ArgumentType::OPTIONAL;
                    }
                }
            } else {
                // Argument is of type any and is optional
                $type = DataTypes::ANY;
                $state = ArgumentType::OPTIONAL;
            }
            $funcArg = new Argument($type, $state);
            if ($funcArg->isOptional()) {
                ++$optinal_args_count;
                $optional_arguments[] = $funcArg;
            }
            if (!$funcArg->isOptional()) {
                ++$required_args_count;
                $required_arguments[] = $funcArg;
            }
            $carr[] = $funcArg;
        }

        return new OverloadMethodArgumentsContainer(
            $this->normalizeTypes([...$required_arguments, ...$optional_arguments]),
            $required_arguments,
            $optional_arguments,
            $required_args_count,
            $optinal_args_count,
        );
    }

    /**
     * Undocumented function.
     *
     * @param NamedArgument[]|Argument[] $types
     *
     * @return Argument[]|Argument[]
     */
    private function normalizeTypes($types)
    {
        return array_map(static function ($type) {
            switch ($type->getType()) {
                case 'int':
                    return new NamedArgument(
                        $type instanceof NamedArgument ? $type->getName() : '*',
                        DataTypes::T_INTEGER,
                        $type->isOptional() ? ArgumentType::OPTIONAL : ArgumentType::REQUIRED
                    );
                case 'bool':
                    return new NamedArgument(
                        $type instanceof NamedArgument ? $type->getName() : '*',
                        DataTypes::T_BOOLEAN,
                        $type->isOptional() ? ArgumentType::OPTIONAL : ArgumentType::REQUIRED
                    );
                default:
                    return $type;
            }
        }, $types);
    }

    /**
     * @param Argument[] $arguments
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    private function matchArgumentsToParameters(array $arguments, array $params)
    {
        return array_reduce(
            Arr::zip($params, $arguments),
            static function ($isMatch, $argAndType) {
                [$arg, $type] = $argAndType;
                if (null === $type) {
                    return null === $arg ? $isMatch && true : $isMatch && false;
                }
                $type_class = \gettype($arg);
                $arg_class = $type->getType();
                $is_arg_instance_of = $arg instanceof $arg_class;
                $arg_null_for_optional = null === $arg && $type->isOptional();

                return $isMatch && ($arg_null_for_optional ||
                    DataTypes::ANY === $arg_class ||
                    $type_class === $arg_class ||
                    $is_arg_instance_of);
            },
            true
        );
    }
}
