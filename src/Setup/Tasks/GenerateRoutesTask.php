<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Tasks;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class GenerateRoutesTask extends Task
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
        $this->info('Generating routes from controller attributes...');

        try {
            $this->ensureSqliteDatabaseExists();
            config(['cache.default' => 'array']);

            // Run the waymaker:generate command
            $exitCode = Artisan::call('waymaker:generate', [], $this->command ? $this->command->getOutput() : null);

            if ($exitCode === 0) {
                $this->info('Routes generated successfully.');

                return true;
            } else {
                $this->error('Failed to generate routes. Exit code: '.$exitCode);

                return false;
            }
        } catch (\Exception $e) {
            $this->error('Failed to generate routes: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Generating routes from controller attributes...';
    }

    private function ensureSqliteDatabaseExists(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if (! is_string($database) || $database === ':memory:' || $database === '') {
            return;
        }

        if ($this->filesystem->exists($database)) {
            return;
        }

        $this->filesystem->ensureDirectoryExists(dirname($database));
        $this->filesystem->put($database, '');
    }
}
