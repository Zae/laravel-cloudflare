<?php
declare(strict_types=1);

namespace Monicahq\Cloudflare\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Monicahq\Cloudflare\CloudflareProxies;
use Monicahq\Cloudflare\Tests\FeatureTestCase;

/**
 * Class MiddlewareTest
 *
 * @package Monicahq\Cloudflare\Tests\Unit
 */
class ReloadCommandTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function it_should_fill_cache_when_reloading(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '0.0.0.0/20'),
            new Response(200, [], '::1/32'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $this->instance(CloudflareProxies::class, $loader);

        $this->assertNull(Cache::get('cloudflare.proxies'));

        $this->artisan('cloudflare:reload')
            ->assertExitCode(0);

        $this->assertEquals(
            [
                '0.0.0.0/20',
                '::1/32'
            ],
            Cache::get('cloudflare.proxies'),
        );
    }

}
