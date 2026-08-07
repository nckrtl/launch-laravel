<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UpdateUsersMigrationTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $path = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (! $this->filesystem->exists($path)) {
            $this->info('Users migration not found. Skipping last_login_at column.');

            return true;
        }

        $contents = $this->filesystem->get($path);

        if (str_contains($contents, 'last_login_at')) {
            $this->info('Users migration already includes last_login_at column.');

            return true;
        }

        $updated = str_replace(
            "            \$table->timestamp('email_verified_at')->nullable();\n",
            "            \$table->timestamp('email_verified_at')->nullable();\n            \$table->timestamp('last_login_at')->nullable();\n",
            $contents
        );

        if ($updated === $contents) {
            $this->error('Failed to locate email_verified_at column in users migration.');

            return false;
        }

        $this->filesystem->put($path, $updated);
        $this->info('Users migration updated with last_login_at column.');

        return true;
    }

    public function description(): string
    {
        return 'Updating users migration for authentication';
    }
}
