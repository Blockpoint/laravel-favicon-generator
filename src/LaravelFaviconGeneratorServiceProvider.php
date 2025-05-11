<?php

namespace Blockpoint\LaravelFaviconGenerator;

use Blockpoint\LaravelFaviconGenerator\Commands\LaravelFaviconGeneratorCommand;
use Blockpoint\LaravelFaviconGenerator\View\Components\FaviconMeta;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelFaviconGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-favicon-generator')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(LaravelFaviconGeneratorCommand::class);
    }

    public function packageBooted(): void
    {
        // Register the favicon-meta component
        Blade::component('favicon-meta', FaviconMeta::class);

        // Register the LaravelFaviconGenerator singleton
        $this->app->singleton(LaravelFaviconGenerator::class, function () {
            return new LaravelFaviconGenerator;
        });
    }

    public function packageRegistered(): void
    {
        // Register view namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'favicon-generator');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/favicon-generator'),
        ], 'favicon-generator-views');
    }
}
