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

final class DataTypes
{
    /**
     * PHP mixed|any variable type.
     */
    const ANY = '*';

    /**
     * PHP double variables' data type.
     */
    const T_DOUBLE = 'double';

    /**
     * PHP boolean variables' data type.
     */
    const T_BOOLEAN = 'boolean';

    /**
     * PHP integer variables' data type.
     */
    const T_INTEGER = 'integer';

    /**
     * PHP string variables' data type.
     */
    const T_STRING = 'string';

    /**
     * PHP array variables' data type.
     */
    const STD_ARRAY = 'array';

    /**
     * PHP stdClass variables' data type.
     */
    const T_OBJECT = 'object';

    /**
     * PHP resource variables' data type
     * 
     * Note: Resource is closed as of PHP 2.0.
     */
    const T_RESOURCE = 'resource';

    /**
     * PHP NoneType variables' data type.
     */
    const T_NULL = 'NULL';
}
