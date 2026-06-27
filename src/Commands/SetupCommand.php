<?php

declare(strict_types=1);

namespace HardImpact\Craft\Commands;

use HardImpact\Craft\Setup\SetupApp;
use HardImpact\Craft\Setup\SetupFilament;
use HardImpact\Craft\Setup\SetupInterface;
use HardImpact\Craft\Setup\SetupMultilanguage;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends Command
{
    private const array SETUPS = [
        'app' => SetupApp::class,
        'filament' => SetupFilament::class,
        'multilanguage' => SetupMultilanguage::class,
    ];

    protected $signature = 'craft:setup {type : The type of setup to run (app, filament, multilanguage)}';

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
        $type = $this->argument('type');

        $setup = $this->resolveSetup($type);

        if (! $setup) {
            $this->error("Setup for '{$type}' not found.");

            return 1;
        }

        // Pass this command instance to the setup
        $setup->setCommand($this);

        // Run the setup
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
