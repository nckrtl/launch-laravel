<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\App;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyFrontendBootstrapTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $files = [
            __DIR__.'/../../../resources/stubs/app/resources/js/app.tsx' => resource_path('js/app.tsx'),
            __DIR__.'/../../../resources/stubs/app/resources/js/types/index.d.ts' => resource_path('js/types/index.d.ts'),
            __DIR__.'/../../../resources/stubs/app/vite.config.ts' => base_path('vite.config.ts'),
        ];

        foreach ($files as $from => $to) {
            if (! $this->copyFile($from, $to)) {
                $this->error("Failed to copy {$from}.");

                return false;
            }
        }

        $this->info('Copied frontend bootstrap files.');

        return true;
    }

    public function description(): string
    {
        return 'Copying frontend bootstrap files';
    }
}
