<?php

namespace Blockpoint\LaravelFaviconGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Blockpoint\LaravelFaviconGenerator\LaravelFaviconGenerator
 */
class LaravelFaviconGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Blockpoint\LaravelFaviconGenerator\LaravelFaviconGenerator::class;
    }
}
