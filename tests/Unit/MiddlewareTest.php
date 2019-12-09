<?php
declare(strict_types=1);

namespace Monicahq\Cloudflare\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;
use Monicahq\Cloudflare\Tests\FeatureTestCase;

/**
 * Class MiddlewareTest
 *
 * @package Monicahq\Cloudflare\Tests\Unit
 */
class MiddlewareTest extends FeatureTestCase
{
    public function test_it_sets_trusted_proxies(): void
    {
        Cache::shouldReceive('get')
            ->with('cloudflare.proxies', [])
            ->andReturn(['expect']);

        $request = new Request();

        (new TrustProxies($this->app->make('config')))->handle($request, function () {
        });

        $this->assertEquals(
            $request::getTrustedProxies(),
            ['expect']
        );
    }

    public function test_it_does_not_sets_trusted_proxies(): void
    {
        Cache::shouldReceive('get')
            ->with('cloudflare.proxies', [])
            ->andReturn([]);

        $request = new Request();

        (new TrustProxies($this->app->make('config')))->handle($request, function () {
        });

        $this->assertEquals(
            $request::getTrustedProxies(),
            []
        );
    }
}
