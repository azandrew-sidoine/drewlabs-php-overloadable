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

class OverloadMethodArgumentsContainer
{
    private $all;
    private $required;
    private $optional;
    private $required_count;
    private $optional_count;

    public function __construct(
        array $all,
        array $required,
        array $optional,
        int $required_count,
        int $optional_count
    ) {
        $this->all = $all;
        $this->required = $required;
        $this->optional = $optional;
        $this->required_count = $required_count;
        $this->optional_count = $optional_count;
    }

    public function count()
    {
        return \count($this->getAll() ?? []);
    }

    public function length()
    {
        return $this->count();
    }

    /**
     * @return FuncArgument[]
     */
    public function getAll()
    {
        return $this->all ?? [];
    }

    /**
     * @return array[]
     */
    public function getRequiredArguments()
    {
        return $this->required ?? [];
    }

    /**
     * @return array[]
     */
    public function getOptionalArguments()
    {
        return $this->optional ?? [];
    }

    /**
     * @return int
     */
    public function requiredArgumentsCount()
    {
        return $this->required_count;
    }

    /**
     * @return int
     */
    public function optionalArgumentsCount()
    {
        return $this->optional_count;
    }

    public function toArray()
    {
        return [
            'all' => $this->all,
            'required' => $this->required,
            'optional' => $this->optional,
            'required_count' => $this->required_count,
            'optional_count' => $this->optional_count,
        ];
    }
}
