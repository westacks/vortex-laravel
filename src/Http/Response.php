<?php

namespace WeStacks\Laravel\Vortex\Http;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;

class Response implements Responsable
{
    use Macroable;

    public function __construct(
        protected string $component,
        protected array $props,
        protected string $view,
        protected ?string $version,
    ) {
    }

    public function toResponse($request)
    {
        $only = array_filter(explode(',', $request->header(Header::ONLY->value, '')));

        $props = $this->prepareProps(
            $this->props,
            ! $needJson = (bool) $request->header(Header::VORTEX->value),
            $only && $request->header(Header::COMPONENT->value) === $this->component ? $only : null,
            $request,
        );

        $page = [
            'component' => $this->component,
            'props' => $props,
            'url' => $request->getRequestUri(),
            'version' => $this->version,
        ];

        return $needJson
            ? new JsonResponse($page, 200, [Header::VORTEX->value => true])
            : view($this->view, ['page' => $page], []);
    }

    protected function prepareProps(array $props, bool $initial, ?array $only, $request, bool $unpack = true): array
    {
        foreach ($props as $key => $value) {
            $onlyLoaded = $initial || ! $only || in_array($key, $only);
            $initLoaded = $initial || ($only && in_array($key, $only));
            $lazyLoaded = ! $initial && $only && in_array($key, $only);

            $unset = new Property(PropertyType::UNSET);

            // Evaluation

            if ($value instanceof Closure) {
                $value = $onlyLoaded ? App::call($value) : $unset;
            }

            if ($value instanceof Property) {
                $value = match ($value->type) {
                    PropertyType::INIT => $initLoaded ? App::call($value) : $unset,
                    PropertyType::LAZY => $lazyLoaded ? App::call($value) : $unset,
                    default => $value,
                };
            }

            if ($value instanceof PromiseInterface) {
                $value = $onlyLoaded ? $value->wait() : $unset;
            }

            if ($value instanceof Property && $value->type === PropertyType::UNSET) {
                unset($props[$key]);

                continue;
            }

            // Post-processing

            if ($value instanceof ResourceResponse || $value instanceof JsonResource) {
                $value = $value->toResponse($request)->getData(true);
            }

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                $subOnly = ! $only ? $only : collect($only)
                    ->filter(fn ($prop) => str_starts_with("$key.", $prop))
                    ->map(fn ($prop) => str_replace("$key.", '', $prop))
                    ->values()
                    ->all();
                $value = $this->prepareProps($value, $initial, $subOnly, $unpack);
            }

            if ($unpack && str_contains($key, '.')) {
                Arr::set($props, $key, $value);
                unset($props[$key]);
            } else {
                $props[$key] = $value;
            }
        }

        return $props;
    }
}
