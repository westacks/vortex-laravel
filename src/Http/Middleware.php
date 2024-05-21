<?php

namespace WeStacks\Laravel\Vortex\Http;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use WeStacks\Laravel\Vortex\Facades\Vortex;

class Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected string $view = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        if (config('app.asset_url')) {
            return md5(config('app.asset_url'));
        }

        if (file_exists($manifest = public_path('mix-manifest.json'))) {
            return md5_file($manifest);
        }

        if (file_exists($manifest = public_path('build/manifest.json'))) {
            return md5_file($manifest);
        }

        return null;
    }

    /**
     * Defines the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return [
            'errors' => $this->resolveValidationErrors($request),
        ];
    }

    /**
     * Sets the root template that's loaded on the first page visit.
     */
    public function view(Request $request): string
    {
        return $this->view;
    }

    /**
     * Handle the incoming request.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        Vortex::version(fn () => $this->version($request));
        Vortex::share($this->share($request));
        Vortex::view($this->view($request));

        $response = $next($request);

        $response->headers->set('Vary', Header::VORTEX->value);

        if (! $request->header(Header::VORTEX->value)) {
            return $response;
        }

        if ($request->method() === 'GET' && $request->header(Header::VERSION->value, '') !== Vortex::version()) {
            $response = $this->onVersionChange($request, $response);
        }

        if ($response->isOk() && empty($response->getContent())) {
            $response = $this->onEmptyResponse($request, $response);
        }

        if ($response->getStatusCode() === 302 && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $response->setStatusCode(303);
        }

        return $response;
    }

    /**
     * Determines what to do when an Inertia action returned with no response.
     * By default, we'll redirect the user back to where they came from.
     */
    public function onEmptyResponse(Request $request, Response $response): Response
    {
        return Redirect::back();
    }

    /**
     * Determines what to do when the Inertia asset version has changed.
     * By default, we'll initiate a client-side location visit to force an update.
     */
    public function onVersionChange(Request $request, Response $response): Response
    {
        if ($request->hasSession()) {
            /** @var \Illuminate\Session\Store */
            $session = $request->session();
            $session->reflash();
        }

        if ($request->vortex()) {
            return response(
                status: 409,
                headers: [Header::LOCATION->value => $request->fullUrl()]
            );
        }

        return redirect()->away($request->fullUrl());
    }

    /**
     * Resolves and prepares validation errors in such a way that they are easier to use client-side.
     */
    public function resolveValidationErrors(Request $request): object
    {
        if (! $request->hasSession() || ! $request->session()->has('errors')) {
            return (object) [];
        }

        return (object) collect($request->session()->get('errors')->getBags())->map(function ($bag) {
            return (object) collect($bag->messages())->map(function ($errors) {
                return $errors[0];
            })->toArray();
        })->pipe(function ($bags) use ($request) {
            if ($bags->has('default') && $request->header(Header::ERRORS->value)) {
                return [$request->header(Header::ERRORS->value) => $bags->get('default')];
            }

            if ($bags->has('default')) {
                return $bags->get('default');
            }

            return $bags->toArray();
        });
    }
}
