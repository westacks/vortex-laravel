<?php

namespace WeStacks\Laravel\Vortex;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use WeStacks\Laravel\Vortex\Testing\TestResponseMixin;

class VortexServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->scoped(Http\Factory::class);
        $this->app->scoped(View\Renderer::class);

        $this->mergeConfigFrom(__DIR__.'/../config/vortex.php', 'vortex');
    }

    public function boot()
    {
        $this->bootShortcuts();

        if ($this->app->runningInConsole()) {
            $this->bootConsole();
        }
    }

    protected function bootShortcuts(): void
    {
        Router::macro('vortex', function (string $uri, string $component, array $props = []) {
            /** @var Router $this */
            return $this->match(['GET', 'HEAD'], $uri, '\\'.Http\Controller::class)
                ->defaults('component', $component)
                ->defaults('props', $props);
        });

        Request::macro('vortex', function () {
            /** @var Request $this */
            return (bool) $this->header(Http\Header::VORTEX->value);
        });

        TestResponse::mixin(new TestResponseMixin);

        Blade::directive('vortex', fn (string $expression = '') =>
            "<?php echo app('".View\Renderer::class."')(\$page, $expression); ?>"
        );
    }

    protected function bootConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/vortex.php' => config_path('vortex.php'),
        ], 'vortex-config');

        $this->commands([
            Commands\CreateMiddleware::class,
            Commands\StartSsr::class,
            Commands\StopSsr::class,
        ]);
    }
}
