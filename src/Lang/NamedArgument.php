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

namespace Drewlabs\Overloadable\Lang;

use Drewlabs\Overloadable\Lang\Traits\Argument;

class NamedArgument
{
    use Argument;

    /**
     * Parameter holding the state of the parameter.
     *
     * @var string|int
     */
    private $state;

    /**
     * Property holding the parameter type.
     *
     * @var string|mixed
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    public function __construct(
        string $name = 'unknown',
        ?string $type = DataTypes::ANY,
        ?string $state = ArgumentType::REQUIRED
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->state = $state;
    }

    /**
     * Handle type conversion to string.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s:%s', $this->getType(), $this->isOptional() ? ArgumentType::OPTIONAL : ArgumentType::REQUIRED);
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
