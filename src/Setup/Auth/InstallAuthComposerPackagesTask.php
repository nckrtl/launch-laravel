<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;
use Symfony\Component\Process\Process;

class InstallAuthComposerPackagesTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $this->info('Installing auth Composer packages...');

        $process = $this->composerProcess();
        $process->setTimeout(300);

        if ($this->command) {
            $process->run(fn ($type, $buffer) => $this->command->line($buffer));
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            $this->error('Failed to install auth Composer packages: '.$process->getErrorOutput());

            return false;
        }

        $this->info('Auth Composer packages installed successfully.');

        return true;
    }

    public function description(): string
    {
        return 'Installing auth Composer packages';
    }

    protected function composerProcess(): Process
    {
        return new Process([
            'composer',
            'require',
            'laravel/fortify:^1.30',
            'laravel/passkeys:^0.2',
            'wnx/laravel-tfa-confirmation:^1.0',
            '-W',
        ], base_path());
    }
}
