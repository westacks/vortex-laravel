<?php

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnusedAliasInspection */

namespace Illuminate\Routing {
    /**
     * @method \Illuminate\Routing\Route vortex(string $uri, string $component, array $props = [])
     */
    class Router
    {
        //
    }
}

namespace Illuminate\Support\Facades {
    /**
     * @method static \Illuminate\Routing\Route vortex(string $uri, string $component, array $props = [])
     */
    class Route
    {
        //
    }
}

namespace Illuminate\Http {
    /**
     * @method bool vortex() Is Vortex request
     */
    class Request
    {
        //
    }
}

namespace Illuminate\Support\Facades {
    /**
     * @method static bool vortex(string $uri, string $component, array $props = []) Is Vortex request
     */
    class Request
    {
        //
    }
}

namespace Illuminate\Testing {

    /**
     * @see \WeStacks\Laravel\Vortex\Testing\TestResponseMixin
     *
     * @method self assertVortex(callable $assert = null) Test Vortex response instance
     * @method array vortexPage() Get the Vortex page
     */
    class TestResponse
    {
        //
    }
}
