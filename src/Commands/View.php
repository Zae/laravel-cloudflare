<?php
declare(strict_types=1);

namespace Monicahq\Cloudflare\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class View
 *
 * @package Monicahq\Cloudflare\Commands
 */
class View extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View list of trust proxies IPs stored in cache.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(): void
    {
        $proxies = Cache::get($this->laravel->make('config')->get('laravelcloudflare.cache'), []);

        $rows = array_map(function ($value) {
            return [
                $value,
            ];
        }, $proxies);

        $this->table([
            'Address',
        ], $rows);
    }
}
