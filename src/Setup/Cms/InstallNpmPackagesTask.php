<?php

namespace HardImpact\Craft\Setup\Cms;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallNpmPackagesTask extends Task
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
        $packages = ['@tailwindcss/forms', '@tailwindcss/typography'];
        $packageManager = $this->packageManager();

        if ($packageManager === null) {
            $this->error('Failed to install npm packages: neither bun nor npm is available.');

            return false;
        }

        $this->info("Installing npm packages via {$packageManager}...");

        $process = $this->packageInstallProcess($packages, $packageManager);

        if ($this->command) {
            $process->run(function ($type, $buffer) {
                $this->command->line($buffer);
            });
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            $this->error('Failed to install npm packages: '.$process->getErrorOutput());

            return false;
        }

        $this->info('NPM packages installed successfully.');

        return true;
    }

    protected function packageInstallProcess(array $packages, string $packageManager): Process
    {
        $command = match ($packageManager) {
            'bun' => ['bun', 'add', '-D'],
            default => ['npm', 'install', '--save-dev'],
        };

        $process = new Process(array_merge($command, $packages), base_path());
        $process->setTimeout(300);

        return $process;
    }

    protected function packageManager(): ?string
    {
        if ($this->executableExists('bun')) {
            return 'bun';
        }

        if ($this->executableExists('npm')) {
            return 'npm';
        }

        return null;
    }

    private function executableExists(string $executable): bool
    {
        $process = new Process(['sh', '-lc', 'command -v "$1"', 'sh', $executable]);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Installing npm packages (@tailwindcss/forms and @tailwindcss/typography)';
    }
}
