<?php

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyLoginLinkConfigTask extends Task
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
        $configStubPath = __DIR__.'/../../../resources/stubs/auth/config/login-link.php';
        $configDestPath = config_path('login-link.php');

        if (! $this->copyFile($configStubPath, $configDestPath)) {
            $this->error('Failed to copy login-link config.');

            return false;
        }

        $this->info('Login-link config copied successfully.');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying login-link config';
    }
}
