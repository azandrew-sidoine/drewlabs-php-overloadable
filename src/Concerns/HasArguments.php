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

namespace Drewlabs\Overloadable\Concerns;

trait HasArguments
{
    /**
     * List of argument passed to the method.
     *
     * @var OverloadMethodArguments
     */
    private $arguments;

    /**
     * {@inheritDoc}
     */
    public function getArguments()
    {
        return array_values(array_map(
            static function ($arg) {
                return (string) $arg;
            },
            $this->arguments->getAll()
        ));
    }

    public function getOptionalArguments()
    {
        return array_values(array_map(
            static function ($arg) {
                return (string) $arg;
            },
            $this->arguments->getOptionalArguments() ?? []
        ));
    }

    public function getRequiredArguments()
    {
        return array_values(array_map(
            static function ($arg) {
                return (string) $arg;
            },
            $this->arguments->getRequiredArguments() ?? []
        ));
    }
}
