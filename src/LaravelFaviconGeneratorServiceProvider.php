<?php

namespace Blockpoint\LaravelFaviconGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Blockpoint\LaravelFaviconGenerator\Commands\LaravelFaviconGeneratorCommand;

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
            ->hasMigration('create_laravel_favicon_generator_table')
            ->hasCommand(LaravelFaviconGeneratorCommand::class);
    }
}
