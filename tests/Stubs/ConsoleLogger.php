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

namespace Drewlabs\Overloadable\Tests\Stubs;

class ConsoleLogger implements Logger, Console
{

    public function write(...$args)
    {
        return implode(" ", ["Writing to console:", ...$args]);
    }
    
    public function log(): string
    {
        return 'Logging to the console...';
    }
}
