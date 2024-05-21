<?php

namespace WeStacks\Laravel\Vortex\Http;

use Illuminate\Http\Request;
use WeStacks\Laravel\Vortex\Facades\Vortex;

class Controller
{
    public function __invoke(Request $request): Response
    {
        return Vortex::render(
            $request->route()->defaults['component'],
            $request->route()->defaults['props']
        );
    }
}
