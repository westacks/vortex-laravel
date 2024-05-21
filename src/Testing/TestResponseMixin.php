<?php

namespace WeStacks\Laravel\Vortex\Testing;

/**
 * @mixin \Illuminate\Testing\TestResponse;
 */
class TestResponseMixin
{
    public function assertVortex()
    {
        return function (callable $callback = null) {
            /** @var \Illuminate\Testing\TestResponse $this */
            $assert = AssertableVortex::fromResponse($this);

            if (is_null($callback)) {
                return $this;
            }

            $callback($assert);

            return $this;
        };
    }

    public function vortexPage()
    {
        return function () {
            /** @var \Illuminate\Testing\TestResponse $this */
            return AssertableVortex::fromResponse($this)->toArray();
        };
    }
}
