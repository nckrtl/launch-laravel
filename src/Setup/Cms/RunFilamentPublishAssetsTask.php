<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Cms;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;
use Symfony\Component\Process\Process;

class RunFilamentPublishAssetsTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $this->info('Publishing Filament assets...');

        $process = new Process(['php', 'artisan', 'filament:assets'], base_path());
        $process->setTimeout(120);

        if ($this->command) {
            $process->run(function ($type, $buffer): void {
                $this->command->line($buffer);
            });
        } else {
            $process->run();
        }

        if (! $process->isSuccessful()) {
            $this->error('Failed to publish Filament assets: '.$process->getErrorOutput());

            return false;
        }

        $this->info('Filament assets published successfully.');

        return true;
    }

    public function description(): string
    {
        return 'Publishing Filament assets';
    }
}
