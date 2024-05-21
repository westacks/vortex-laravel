<?php

namespace WeStacks\Laravel\Vortex\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use WeStacks\Laravel\Vortex\View\Renderer;

#[AsCommand(name: 'vortex:start-ssr')]
class StartSsr extends Command
{
    protected $signature = 'vortex:start-ssr';
    protected $description = 'Start the Vortex SSR server';

    public function handle(Renderer $renderer): int
    {
        if (! config('vortex.ssr.enabled', true)) {
            $this->error('Vortex SSR is not enabled. Enable it via the `vortex.ssr.enabled` config option.');

            return self::FAILURE;
        }

        if (! $bundle = $renderer->bundle()) {
            $this->error('Vortex SSR bundle not found.');

            return self::FAILURE;
        }

        $runtime = config('vortex.ssr.runtime', 'node');

        $this->callSilently(StopSsr::class);

        $this->info('Running Vortex SSR server...');

        $process = Process::forever()->start("$runtime $bundle",
            fn (string $type, string $output) => match ($type) {
                'output' => $this->info(trim($output), OutputStyle::VERBOSITY_VERBOSE),
                'err' => $this->error(trim($output), OutputStyle::OUTPUT_NORMAL),
                default => $this->line(trim($output), null, OutputStyle::VERBOSITY_VERBOSE),
            }
        );

        if (extension_loaded('pcntl')) {
            $this->trap([SIGINT, SIGQUIT, SIGTERM], fn (int $sig) => $process->signal($sig));
        }

        return $process->wait()->exitCode();
    }
}
