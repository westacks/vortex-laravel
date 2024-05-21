<?php

namespace WeStacks\Laravel\Vortex\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class Factory
{
    protected string $view = 'app';

    protected array $shared = [];

    /** @var callable|string|null */
    protected $version = null;

    public function view(?string $view = null): ?string
    {
        if ($view) {
            $this->view = $view;
        }

        return $this->view;
    }

    public function version(callable|string|null|false $version = false): ?string
    {
        if ($version !== false) {
            $this->version = $version;
        }

        return is_callable($this->version) ? App::call($this->version) : $this->version;
    }

    public function share(string|array|Arrayable $key, $value = null): void
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } elseif ($key instanceof Arrayable) {
            $this->shared = array_merge($this->shared, $key->toArray());
        } else {
            Arr::set($this->shared, $key, $value);
        }
    }

    public function shared(?string $key = null, $default = null)
    {
        if ($key) {
            return Arr::get($this->shared, $key, $default);
        }

        return $this->shared;
    }

    public function flush(): void
    {
        $this->shared = [];
    }

    public function lazy(callable $callback): Property
    {
        return new Property(PropertyType::LAZY, $callback);
    }

    public function init(callable $callback): Property
    {
        return new Property(PropertyType::INIT, $callback);
    }

    public function render(string $component, array|Arrayable $props = []): Response
    {
        if ($props instanceof Arrayable) {
            $props = $props->toArray();
        }

        return new Response(
            $component,
            array_merge($this->shared, $props),
            $this->view(),
            $this->version(),
        );
    }
}
