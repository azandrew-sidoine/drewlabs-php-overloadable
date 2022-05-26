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

use Drewlabs\Overloadable\Lang\Traits\Argument as TraitsFuncArgument;

class Argument
{
    use TraitsFuncArgument;

    /**
     * Parameter holding the state of the parameter.
     *
     * @see ./FuncParameterEnum.php
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

    public function __construct(
        ?string $type = DataTypes::ANY,
        ?string $state = ArgumentType::REQUIRED
    ) {
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
}
