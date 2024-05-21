<?php

namespace WeStacks\Laravel\Vortex\Http;

enum Header: string
{
    case VORTEX = 'X-Vortex';
    case ONLY = 'X-Vortex-Only';
    case COMPONENT = 'X-Vortex-Component';
    case VERSION = 'X-Vortex-Version';
    case LOCATION = 'X-Vortex-Location';
    case ERRORS = 'X-Vortex-Errors';
}
