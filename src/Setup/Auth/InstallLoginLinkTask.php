<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use NckRtl\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallLoginLinkTask extends Task
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
        $this->info('Installing spatie/laravel-login-link via Composer...');

        $process = new Process(['composer', 'require', 'spatie/laravel-login-link', '--dev'], base_path());
        $process->setTimeout(300); // 5 minutes timeout

        if ($this->command) {
            $process->run(function ($type, $buffer) {
                $this->command->line($buffer);
            });
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            $this->error('Failed to install spatie/laravel-login-link: '.$process->getErrorOutput());

            return false;
        }

        $this->info('spatie/laravel-login-link installed successfully.');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Installing spatie/laravel-login-link package';
    }
}
