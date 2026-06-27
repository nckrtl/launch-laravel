<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Cms;

use HardImpact\Craft\Setup\SetupAuth;
use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class RunSetupAuthTask extends Task
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
        $this->info('Running authentication setup...');

        $SetupAuth = new SetupAuth($this->filesystem);

        if ($this->command) {
            $SetupAuth->setCommand($this->command);
        }

        try {
            $exitCode = $SetupAuth->setup();

            if ($exitCode === 0) {
                $this->info('Authentication setup completed successfully.');

                return true;
            } else {
                $this->error('Authentication setup failed with exit code: '.$exitCode);

                return false;
            }
        } catch (\Exception $e) {
            $this->error('Authentication setup failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Running authentication setup';
    }
}
