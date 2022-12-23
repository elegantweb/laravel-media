<?php

namespace Elegant\Media;

use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    protected array $commands = [
        Console\CleanCommand::class,
        Console\RegenerateCommand::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootConsole();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/media.php', 'media');
    }

    /**
     * Bootstrap console related services.
     *
     * @return void
     */
    protected function bootConsole(): void
    {
        $this->commands($this->commands);

        $this->publishes([__DIR__.'/../config' => config_path()], 'laravel-media-config');
        $this->publishes([__DIR__.'/../database/migrations/0000_00_00_000000_create_media_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_media_table.php')], 'laravel-media-migrations');
    }
}
