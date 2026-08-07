<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\MultiLanguage;

use HardImpact\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyExamplePageTask extends Task
{
    /**
     * Create a new task instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    /**
     * Run the task.
     */
    public function run(): bool
    {
        $stubPath = __DIR__.'/../../../resources/stubs/multi-language/resources/js/pages/TranslationExample.tsx';
        $destinationPath = resource_path('js/pages/TranslationExample.tsx');

        $this->filesystem->ensureDirectoryExists(resource_path('js/pages'));

        if ($this->copyFile($stubPath, $destinationPath)) {
            $this->info('Translation example page copied successfully.');

            return true;
        }

        $this->error('Failed to copy translation example page.');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying translation example page';
    }
}
