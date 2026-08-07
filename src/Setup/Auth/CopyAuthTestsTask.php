<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use NckRtl\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyAuthTestsTask extends Task
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
        $from = __DIR__.'/../../../resources/stubs/auth/tests';
        $to = base_path('tests');

        $replacements = [
            '{{namespace}}' => app()->getNamespace(),
        ];

        if ($this->copyDirectory($from, $to, $replacements)) {
            $this->info('Copied auth tests.');

            return true;
        }

        $this->error('Failed to copy auth tests.');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying auth tests...';
    }
}
