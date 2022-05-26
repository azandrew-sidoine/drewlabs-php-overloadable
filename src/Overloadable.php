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

use Drewlabs\Core\Helpers\Arr;
use Drewlabs\Core\Helpers\Iter;
use Drewlabs\Core\Helpers\Str;
use Drewlabs\Overloadable\Lang\DataTypes;

trait Overloadable
{
    /**
     * Provide a method overload implementations to PHP classes.
     *
     * @param array $args
     * @param array $signatures
     *
     * @return mixed
     */
    public function overload($args, $signatures)
    {
        $fallbacks = [];
        $handlers = Iter::filter(
            Iter::map(
                new \ArrayIterator($signatures ?? []),
                function ($value, $key) {
                    return new OverloadedMethodHandler($value, $key, $this);
                }
            ),
            static function (OverloadedMethodHandler $candidate) use ($args, $fallbacks) {
                $matches = $candidate->matches($args ?? []);
                if ($candidate->isFallback()) {
                    $fallbacks[] = $candidate;
                }

                return $matches;
            },
            false
        );
        $handlers = iterator_to_array($handlers);
        $total_handlers = \count($handlers);
        if (
            1 === $total_handlers &&
            (1 === \count($fallbacks)) &&
            (null !== $method = $fallbacks[0])
        ) {
            return $method->call($args);
        } elseif (1 === $total_handlers) {
            if ($method = $this->getMethod($handlers)) {
                return $method->call($args);
            }
        } else {
            // Look for the method having a more specific argument type definition
            $handler = Iter::reduce(
                new \ArrayIterator($handlers),
                static function ($carry, $curr) {
                    if (null === $carry) {
                        return $curr;
                    }
                    $arguments = $curr->getArguments();
                    $carry_arguments = $carry->getArguments();
                    foreach (Arr::zip($arguments, $carry_arguments) as $value) {
                        if (Str::contains($value[0] ?? '', sprintf('%s:', DataTypes::ANY))) {
                            $carry = $carry;
                            break;
                        }
                        if (Str::contains($value[1] ?? '', sprintf('%s:', DataTypes::ANY))) {
                            $carry = $curr;
                            break;
                        }
                    }

                    return $carry;
                },
                null
            );
            if ($handler) {
                return $handler->call($args);
            }
            throw new OverloadMethodCallExpection(sprintf('%d method provide the same method definition', $total_handlers));
        }
        throw new OverloadMethodCallExpection('None suitable overloaded method found.');
    }

    /**
     * @return OverloadedMethodHandler
     */
    private function getMethod($values)
    {
        return $values[0] ?? null;
    }
}
