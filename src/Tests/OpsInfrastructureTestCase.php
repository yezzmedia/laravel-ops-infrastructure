<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Tests;

use YezzMedia\Foundation\Testing\FoundationTestCase;
use YezzMedia\OpsInfrastructure\OpsInfrastructureServiceProvider;

/**
 * Provides a realistic Testbench baseline for ops-infrastructure package tests.
 *
 * Sets up the in-memory SQLite database and registers the ops-infrastructure
 * service provider so that all package services are available during tests.
 */
abstract class OpsInfrastructureTestCase extends FoundationTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            OpsInfrastructureServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('ops-infrastructure.cache.enabled', false);
        $app['config']->set('ops-infrastructure.cache.store', null);
        $app['config']->set('ops-infrastructure.audit.driver', null);
    }
}
