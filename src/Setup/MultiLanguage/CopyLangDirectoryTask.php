<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\MultiLanguage;

use NckRtl\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyLangDirectoryTask extends Task
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
        $stubPath = __DIR__.'/../../../resources/stubs/multi-language/lang';
        $destinationPath = base_path('lang');

        if ($this->filesystem->exists($destinationPath)) {
            $this->info('Lang directory already exists. Merging contents...');
        }

        if ($this->copyDirectory($stubPath, $destinationPath)) {
            $this->info('Language files copied successfully.');

            return true;
        }

        $this->error('Failed to copy language files.');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying language files to project root';
    }
}
