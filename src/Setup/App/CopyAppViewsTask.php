<?php

namespace HardImpact\Craft\Setup\App;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyAppViewsTask extends Task
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
        $replacements = [
            '{{namespace}}' => app()->getNamespace(),
        ];

        $success = true;

        // Copy layouts directory
        $layoutsFrom = __DIR__.'/../../../resources/stubs/app/resources/js/layouts';
        $layoutsTo = resource_path('js/layouts');

        if (! $this->copyDirectory($layoutsFrom, $layoutsTo, $replacements)) {
            $this->error('Failed to copy app layouts.');
            $success = false;
        }

        // Copy pages directory
        $pagesFrom = __DIR__.'/../../../resources/stubs/app/resources/js/pages';
        $pagesTo = resource_path('js/pages');

        if (! $this->copyDirectory($pagesFrom, $pagesTo, $replacements)) {
            $this->error('Failed to copy app pages.');
            $success = false;
        }

        // Copy types directory
        $typesFrom = __DIR__.'/../../../resources/stubs/app/resources/js/types';
        $typesTo = resource_path('js/types');

        if (! $this->copyDirectory($typesFrom, $typesTo, $replacements)) {
            $this->error('Failed to copy app types.');
            $success = false;
        }

        if ($success) {
            $this->info('Copied app views and types.');

            return true;
        }

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying app views...';
    }
}
