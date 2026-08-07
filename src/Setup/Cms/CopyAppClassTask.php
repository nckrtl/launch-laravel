<?php

namespace HardImpact\Craft\Setup\Cms;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyAppClassTask extends Task
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
        $from = __DIR__.'/../../../resources/stubs/cms/app/App.php';
        $to = app_path('App.php');

        // Skip if file already exists (might have been added by auth scaffolder)
        if ($this->filesystem->exists($to)) {
            $this->info('App class already exists.');

            return true;
        }

        $replacements = [
            '{{namespace}}' => rtrim(app()->getNamespace(), '\\'),
        ];

        if ($this->copyFile($from, $to, $replacements)) {
            $this->info('Copied App class.');

            return true;
        }

        $this->error('Failed to copy App class.');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying App class...';
    }
}
