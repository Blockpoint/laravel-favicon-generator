<?php

namespace Blockpoint\LaravelFaviconGenerator\Commands;

use Blockpoint\LaravelFaviconGenerator\LaravelFaviconGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LaravelFaviconGeneratorCommand extends Command
{
    public $signature = 'favicon:generate {source : Path to the source image file}'
        .' {--force : Force overwrite existing favicons}'
        .' {--name= : Application name for the web manifest}'
        .' {--short-name= : Short application name for the web manifest}'
        .' {--theme-color= : Theme color for the web manifest}'
        .' {--background-color= : Background color for the web manifest}';

    public $description = 'Generate favicon files from a source image';

    public function handle(LaravelFaviconGenerator $generator): int
    {
        $sourcePath = $this->argument('source');
        $force = $this->option('force');

        // Check if source file exists
        if (! File::exists($sourcePath)) {
            $this->error("Source file not found: {$sourcePath}");

            return self::FAILURE;
        }

        // Check if output directory already has favicons
        $outputPath = config('favicon-generator.output_path', 'favicon');
        $outputDir = public_path($outputPath);

        if (File::exists($outputDir) && ! $force && File::isDirectory($outputDir) && count(File::files($outputDir)) > 0) {
            if (! $this->confirm('Favicon files already exist. Do you want to overwrite them?')) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        // Get manifest options with defaults from config
        $configName = config('favicon-generator.web_manifest.content.name', '');
        $configShortName = config('favicon-generator.web_manifest.content.short_name', '');
        $configThemeColor = config('favicon-generator.web_manifest.content.theme_color', '#ffffff');
        $configBgColor = config('favicon-generator.web_manifest.content.background_color', '#ffffff');

        // Ask for application name if not provided
        $name = $this->option('name');
        if (is_null($name)) {
            $name = $this->ask('Application name for web manifest', $configName);
        }

        // Ask for short name if not provided
        $shortName = $this->option('short-name');
        if (is_null($shortName)) {
            $shortName = $this->ask('Short application name for web manifest', $configShortName);
        }

        // Ask for theme color if not provided
        $themeColor = $this->option('theme-color');
        if (is_null($themeColor)) {
            $themeColor = $this->ask('Theme color (hexadecimal)', $configThemeColor);
        }

        // Ask for background color if not provided
        $bgColor = $this->option('background-color');
        if (is_null($bgColor)) {
            $bgColor = $this->ask('Background color (hexadecimal)', $configBgColor);
        }

        // Prepare manifest options
        $manifestOptions = [
            'name' => $name,
            'short_name' => $shortName,
            'theme_color' => $themeColor,
            'background_color' => $bgColor,
        ];

        $this->info('Generating favicons...');

        try {
            $generatedFiles = $generator->generate($sourcePath, $manifestOptions);

            $this->info('Favicons generated successfully!');
            $this->info('Generated files:');

            foreach ($generatedFiles as $file) {
                $this->line(" - {$file}");
            }

            $this->info('\nTo use the favicons in your application, add the following component to your layout:');
            $this->line('<x-favicon-meta />');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating favicons: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
