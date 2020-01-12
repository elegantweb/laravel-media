<?php

namespace Elegant\Media;

use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Console\CleanCommand::class,
        Console\RegenerateCommand::class,
    ];

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerConsole();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/media.php', 'media');
    }

    /**
     * Register console related dependencies.
     *
     * @return void
     */
    protected function registerConsole(): void
    {
        $this->commands($this->commands);

        $this->publishes([__DIR__.'/../config' => config_path()], 'laravel-media-config');
        $this->publishes([__DIR__.'/../database/migrations/create_media_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_media_table.php')], 'laravel-media-migrations');
    }
}
