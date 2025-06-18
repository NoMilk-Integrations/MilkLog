<?php

namespace RootAccessPlease\MilkLog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService title(?string $message = null)
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService alert(string $message, array $context = [])
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService error(string $message, array $context = [])
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService info(string $message, array $context = [])
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService channel(string $channel)
 * @method static \RootAccessPlease\MilkLog\Services\MilkLogService tags(array $tags)
 * @method static void inform()
 *
 * @see \RootAccessPlease\MilkLog\Services\MilkLogService
 */
class MilkLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'milklog';
    }
}