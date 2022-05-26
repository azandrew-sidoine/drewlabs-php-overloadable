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

class DataTypes
{
    /**
     * PHP mixed|any variable type.
     */
    public const ANY = '*';

    /**
     * PHP double variables' data type.
     */
    public const T_DOUBLE = 'double';

    /**
     * PHP boolean variables' data type.
     */
    public const T_BOOLEAN = 'boolean';

    /**
     * PHP integer variables' data type.
     */
    public const T_INTEGER = 'integer';

    /**
     * PHP string variables' data type.
     */
    public const T_STRING = 'string';

    /**
     * PHP array variables' data type.
     */
    public const STD_ARRAY = 'array';

    /**
     * PHP stdClass variables' data type.
     */
    public const T_OBJECT = 'object';

    /**
     * PHP resource variables' data type
     * 
     * Note: Resource is closed as of PHP 2.0.
     */
    public const T_RESOURCE = 'resource';

    /**
     * PHP NoneType variables' data type.
     */
    public const T_NULL = 'NULL';
}
