<?php

namespace Elegant\Media\Tests;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Elegant\Media\MediaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return __NAMESPACE__ . '\Database\Factories' . Str::after($modelName, __NAMESPACE__ . '\Fixtures\Models') . 'Factory';
        });
    }

    protected function getPackageProviders($app)
    {
        return [MediaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
