<?php

declare(strict_types=1);

namespace NckRtl\Launch\Commands;

use NckRtl\Launch\Setup\SetupApp;
use NckRtl\Launch\Setup\SetupFilament;
use NckRtl\Launch\Setup\SetupInterface;
use NckRtl\Launch\Setup\SetupMultilanguage;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends Command
{
    private const array SETUPS = [
        'app' => SetupApp::class,
        'filament' => SetupFilament::class,
        'multilanguage' => SetupMultilanguage::class,
    ];

    protected $signature = 'launch:setup {type : The type of setup to run (app, filament, multilanguage)}';

    protected $description = 'Setup Launch features';

    protected Filesystem $filesystem;

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
