<?php

declare(strict_types=1);

namespace HardImpact\Craft\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CraftCommand extends Command
{
    protected $signature = 'craft {action : The Craft action to run (setup:app, setup:filament)}';

    protected $description = 'Run Craft actions';

    public function __construct(private readonly Filesystem $filesystem)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');

        if (! str_starts_with($action, 'setup:')) {
            $this->error("Craft action '{$action}' not found.");

            return 1;
        }

        $type = substr($action, strlen('setup:'));

        return (new SetupCommand($this->filesystem))->runSetup($type, $this);
    }
}
