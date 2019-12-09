<?php
declare(strict_types=1);

namespace Monicahq\Cloudflare\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Monicahq\Cloudflare\CloudflareProxies;
use Monicahq\Cloudflare\Tests\FeatureTestCase;
use UnexpectedValueException;

/**
 * Class CloudflareProxiesTest
 *
 * @package Monicahq\Cloudflare\Tests\Unit
 */
class CloudflareProxiesTest extends FeatureTestCase
{
    public function test_load_empty(): void
    {
        $loader = new CloudflareProxies($this->app->make('config'));

        $ips = $loader->load(0);

        $this->assertNotNull($ips);
        $this->assertCount(0, $ips);
    }

    public function test_load_ipv4(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '0.0.0.0/20'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $ips = $loader->load(CloudflareProxies::IP_VERSION_4);

        $this->assertNotNull($ips);
        $this->assertEquals([
            '0.0.0.0/20',
        ], $ips);
    }

    public function test_load_ipv6(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '::1/32'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $ips = $loader->load(CloudflareProxies::IP_VERSION_6);

        $this->assertNotNull($ips);
        $this->assertEquals([
            '::1/32',
        ], $ips);
    }

    public function test_load_all(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '0.0.0.0/20'),
            new Response(200, [], '::1/32'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $ips = $loader->load(CloudflareProxies::IP_VERSION_ANY);

        $this->assertNotNull($ips);
        $this->assertEquals([
            '0.0.0.0/20',
            '::1/32',
        ], $ips);
    }

    public function test_load_default(): void
    {
        $me = $this;
        $mock = new MockHandler([
            new Response(200, [], '0.0.0.0/20'),
            new Response(200, [], '::1/32'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $ips = $loader->load();

        $this->assertNotNull($ips);

        $this->assertEquals([
            '0.0.0.0/20',
            '::1/32',
        ], $ips);
    }

    public function test_load_exception(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $mock = new MockHandler([
            new Response(500, [], ''),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $loader->load();
    }

    public function test_load_not_200(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $mock = new MockHandler([
            new Response(210, [], ''),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $loader->load();
    }

    public function test_right_urls(): void
    {
        $me = $this;
        $mock = new MockHandler([
            function (\Psr\Http\Message\RequestInterface $request, array $options) use ($me) {
                $me->assertEquals('https://www.cloudflare.com/ips-v4', (string) $request->getUri());

                return new Response(200, [], '0.0.0.0/20');
            },
            function (\Psr\Http\Message\RequestInterface $request, array $options) use ($me) {
                $me->assertEquals('https://www.cloudflare.com/ips-v6', (string) $request->getUri());

                return new Response(200, [], '::1/32');
            },
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $loader = new CloudflareProxies($this->app->make('config'), $client);

        $loader->load();
    }

    public function test_create_guzzle(): void
    {
        $loader = new CloudflareProxies($this->app->make('config'));

        $reflection = new \ReflectionClass(CloudflareProxies::class);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        $client = $property->getValue($loader);

        $this->assertNotNull($client);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_create_class(): void
    {
        $loader = $this->app->make(CloudflareProxies::class);

        $this->assertInstanceOf(CloudflareProxies::class, $loader);
    }
}
