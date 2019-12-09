<?php
declare(strict_types=1);

namespace Monicahq\Cloudflare\Tests;

use Monicahq\Cloudflare\TrustedProxyServiceProvider;
use Orchestra\Testbench\TestCase;

/**
 * Class FeatureTestCase
 *
 * @package Monicahq\Cloudflare\Tests
 */
class FeatureTestCase extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            TrustedProxyServiceProvider::class,
        ];
    }

    /**
     * Resolve application core implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function resolveApplicationCore($app): void
    {
        parent::resolveApplicationCore($app);

        $app->detectEnvironment(function () {
            return 'testing';
        });
    }
}
