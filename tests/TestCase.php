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

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return __NAMESPACE__ . '\Database\Factories' . Str::after($modelName, __NAMESPACE__ . '\Fixtures\Models') . 'Factory';
        });
    }

    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[RefreshStorage::class])) {
            $this->refreshStorage();
        }
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

        $app['config']->set('media.disk', 'testbench');

        $app['config']->set('filesystems.default', 'testbench');
        $app['config']->set('filesystems.disks.testbench', [
            'driver' => 'local',
            'root' => __DIR__ . '/storage',
        ]);
    }
}
