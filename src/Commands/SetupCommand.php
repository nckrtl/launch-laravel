<?php

declare(strict_types=1);

namespace HardImpact\Craft\Commands;

use HardImpact\Craft\Setup\SetupApp;
use HardImpact\Craft\Setup\SetupAuth;
use HardImpact\Craft\Setup\SetupDashboard;
use HardImpact\Craft\Setup\SetupFilament;
use HardImpact\Craft\Setup\SetupInterface;
use HardImpact\Craft\Setup\SetupMultilanguage;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends Command
{
    private const array SETUPS = [
        'app' => SetupApp::class,
        'auth' => SetupAuth::class,
        'dashboard' => SetupDashboard::class,
        'filament' => SetupFilament::class,
        'multilanguage' => SetupMultilanguage::class,
    ];

    protected $signature = 'craft:setup {type : The type of setup to run (app, auth, dashboard, filament, multilanguage)}';

    protected $description = 'Setup Craft features';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle(): int
    {
        return $this->runSetup((string) $this->argument('type'), $this);
    }

    public function runSetup(string $type, Command $command): int
    {
        $setup = $this->resolveSetup($type);

        if (! $setup) {
            $command->error("Setup for '{$type}' not found.");

            return 1;
        }

        $setup->setCommand($command);

        return $setup->setup();
    }

    protected function resolveSetup(string $type): ?SetupInterface
    {
        $setupClass = self::SETUPS[$type] ?? null;

        if ($setupClass === null) {
            return null;
        }

        return new $setupClass($this->filesystem);
    }
}
