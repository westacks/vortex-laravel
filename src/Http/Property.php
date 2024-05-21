<?php

namespace WeStacks\Laravel\Vortex\Http;

class Property
{
    protected $callback = null;

    public function __construct(
        readonly public PropertyType $type,
        ?callable $callback = null,
    ) {
        $this->callback = $callback;
    }

    public function __invoke(...$arguments)
    {
        if ($this->callback === null) {
            return null;
        }

        return call_user_func_array($this->callback, $arguments);
    }
}
