<?php

namespace WeStacks\Laravel\Vortex\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'vortex:stop-ssr')]
class StopSsr extends Command
{
    protected $name = 'vortex:stop-ssr';
    protected $description = 'Stop the Vortex SSR server';

    public function handle(): int
    {
        $ch = curl_init(config('vortex.ssr.url', 'http://127.0.0.1:13714')."/down");
        curl_exec($ch);

        if (curl_error($ch) !== 'Empty reply from server') {
            $this->error('Unable to connect to Vortex SSR server.');

            return self::FAILURE;
        }

        $this->info('Vortex SSR server stopped.');

        curl_close($ch);

        return self::SUCCESS;
    }
}
