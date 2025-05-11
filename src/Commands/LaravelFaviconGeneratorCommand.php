<?php

namespace Blockpoint\LaravelFaviconGenerator\Commands;

use Illuminate\Console\Command;

class LaravelFaviconGeneratorCommand extends Command
{
    public $signature = 'laravel-favicon-generator';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
