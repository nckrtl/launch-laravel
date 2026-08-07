<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Cms;

use HardImpact\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallFilamentComposerPackageTask extends Task
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
        $this->info('Installing Filament package via Composer...');

        $process = new Process(['composer', 'require', 'filament/filament:^5.2', '-W'], base_path());
        $process->setTimeout(300); // 5 minutes timeout

        if ($this->command) {
            $process->run(function ($type, $buffer) {
                $this->command->line($buffer);
            });
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            $this->error('Failed to install Filament package: '.$process->getErrorOutput());

            return false;
        }

        $this->info('Filament package installed successfully.');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Installing Filament composer package';
    }
}
