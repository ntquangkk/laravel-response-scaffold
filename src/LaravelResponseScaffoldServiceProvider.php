<?php

namespace TriQuang\LaravelResponseScaffold;

use Illuminate\Support\ServiceProvider;
use TriQuang\LaravelResponseScaffold\Commands\MakeResponseScaffoldCommand;

class LaravelResponseScaffoldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Register the command
            $this->commands([
                MakeResponseScaffoldCommand::class,
            ]);

            // Publish stubs for customization
            $this->publishes([
                __DIR__ . '/../stubs' => base_path('stubs/vendor/triquang/laravel-response-scaffold'),
            ], 'response-scaffold-stubs');
        }
    }

    public function register() {}
}
