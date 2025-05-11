<?php

namespace Blockpoint\LaravelFaviconGenerator\Commands;

use Blockpoint\LaravelFaviconGenerator\LaravelFaviconGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LaravelFaviconGeneratorCommand extends Command
{
    public $signature = 'favicon:generate {source : Path to the source image file}'
        . ' {--force : Force overwrite existing favicons}';

    public $description = 'Generate favicon files from a source image';

    public function handle(LaravelFaviconGenerator $generator): int
    {
        $sourcePath = $this->argument('source');
        $force = $this->option('force');

        // Check if source file exists
        if (!File::exists($sourcePath)) {
            $this->error("Source file not found: {$sourcePath}");
            return self::FAILURE;
        }

        // Check if output directory already has favicons
        $outputPath = config('favicon-generator.output_path', 'favicon');
        $outputDir = public_path($outputPath);

        if (File::exists($outputDir) && !$force && File::isDirectory($outputDir) && count(File::files($outputDir)) > 0) {
            if (!$this->confirm('Favicon files already exist. Do you want to overwrite them?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Generating favicons...');

        try {
            $generatedFiles = $generator->generate($sourcePath);

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
