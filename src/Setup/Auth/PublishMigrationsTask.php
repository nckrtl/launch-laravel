<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use NckRtl\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishMigrationsTask extends Task
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
        $this->info('Publishing authentication migrations...');

        $packageMigrationPath = __DIR__.'/../../../resources/stubs/auth/database/migrations';
        $destinationPath = database_path('migrations');
        $migrations = [
            'add_two_factor_columns_to_users_table.php' => '2026_07_01_000001_add_two_factor_columns_to_users_table.php',
            'create_passkeys_table.php' => '2026_07_01_000002_create_passkeys_table.php',
        ];

        if (! $this->filesystem->isDirectory($packageMigrationPath)) {
            $this->error("Migration source directory not found: {$packageMigrationPath}");

            return false;
        }

        $this->filesystem->ensureDirectoryExists($destinationPath);

        foreach ($migrations as $source => $destination) {
            if ($this->migrationExists($destinationPath, $source)) {
                $this->info("Migration {$source} already exists. Skipping.");

                continue;
            }

            $this->filesystem->copy(
                $packageMigrationPath.'/'.$source,
                $destinationPath.'/'.$destination
            );
            $this->info("Copied migration: {$destination}");
        }

        $this->info('Migrations published successfully.');

        return true;
    }

    /**
     * Check if a migration already exists.
     */
    protected function migrationExists(string $directory, string $fileName): bool
    {
        // Extract the descriptive part of the migration name
        $migrationName = substr($fileName, strpos($fileName, '_') + 1);

        // Get all migration files
        $files = $this->filesystem->files($directory);

        // Check if any existing migration contains the same descriptive name
        foreach ($files as $file) {
            $existingName = $file->getFilename();
            if (strpos($existingName, $migrationName) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Publishing authentication migrations';
    }
}
