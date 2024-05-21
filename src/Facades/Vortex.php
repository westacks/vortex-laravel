<?php

namespace WeStacks\Laravel\Vortex\Facades;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Facade;
use WeStacks\Laravel\Vortex\Http\Factory;
use WeStacks\Laravel\Vortex\Http\Property;
use WeStacks\Laravel\Vortex\Http\Response;

/**
 * @method static string view(string $name = null)
 * @method static ?string version(callable|string|null $version = false)
 * @method static void share(string|array|Arrayable $key, mixed $value = null)
 * @method static mixed shared(string|null $key = null, mixed $default = null)
 * @method static void flush()
 * @method static Property lazy(callable $callback)
 * @method static Property init(callable $callback)
 * @method static Response render(string $component, array|Arrayable $props = [])
 *
 * @see Factory
 */
class Vortex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
