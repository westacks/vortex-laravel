<?php

namespace WeStacks\Laravel\Vortex\View;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use WeStacks\Laravel\Vortex\Exceptions\SilentException;

class Renderer
{
    protected array $content = [];

    public function __invoke(array $page, string $module = 'body', string $id = 'app'): string
    {
        $content = $this->render($module, json_encode($page)) ?? '';

        return match ($module) {
            'body' => '<div id="'.$id.'" data-page="'.htmlspecialchars(json_encode($page), ENT_QUOTES, 'UTF-8').'"'.($content ? ' data-ssr="true"' : '').'>'.$content.'</div>',
            default => $content,
        }.PHP_EOL;
    }

    public function bundle(): ?string
    {
        return collect(config('vortex.ssr.bundle'))->filter()->first(fn ($path) => file_exists($path));
    }

    protected function render(string $module, string $page): ?string
    {
        if (! config('vortex.ssr.enabled', true)) {
            return null;
        }

        if (isset($this->content[$key = md5($page)])) {
            return $this->renderModule($key, $module);
        }

        $resolve = fn () => match ($mode = config('vortex.ssr.mode', 'cli')) {
            'cli' => $this->renderCli($page),
            'server' => $this->renderServer($page),
            default => throw new \Exception("Invalid Vortex SSR mode: '$mode'"),
        };

        $this->content[$key] = rescue(fn () => ($cacheTime = config('vortex.ssr.cache', 0))
            ? Cache::remember("vortex:$key", $cacheTime, $resolve)
            : $resolve(),
            report: fn ($e) => !($e instanceof SilentException),
        );

        return $this->renderModule($key, $module);
    }

    protected function renderModule(string $key, string $module): ?string
    {
        $result = $this->content[$key][$module] ?? null;

        return is_array($result) ? implode($result) : $result;
    }

    protected function renderCli(string $input): ?array
    {
        if (! $bundle = $this->bundle()) {
            throw new SilentException('Vortex SSR bundle not found.');
        }

        $runtime = config('vortex.ssr.runtime', 'node');
        $input = str_replace("'", "'\\''", $input);

        $process = Process::run("$runtime $bundle '$input'")->throw();

        return @json_decode($process->output(), true);
    }

    protected function renderServer(string $input): ?array
    {
        return Http::baseUrl(config('vortex.ssr.url', 'http://127.0.0.1:13714'))
            ->withBody($input)
            ->throw()
            ->post('/render')
            ->json();
    }
}
