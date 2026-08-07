<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\App;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;

class CopyAppMiddlewareTask extends Task
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
        $from = __DIR__.'/../../../resources/stubs/app/app/Http/Middleware/HandleInertiaRequests.php';
        $to = app_path('Http/Middleware/HandleInertiaRequests.php');

        $replacements = [
            '{{namespace}}' => app()->getNamespace(),
        ];

        // Check if the middleware already exists
        if ($this->filesystem->exists($to)) {
            $this->info('HandleInertiaRequests middleware already exists. Replacing...');
        }

        // Copy the file (this will overwrite if it exists)
        if ($this->copyFile($from, $to, $replacements)) {
            $this->info('Copied HandleInertiaRequests middleware.');

            return true;
        }

        $this->error('Failed to copy HandleInertiaRequests middleware.');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying app middleware...';
    }
}
