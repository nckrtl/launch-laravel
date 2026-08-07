<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use NckRtl\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UpdateDatabaseSeederTask extends Task
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
        $seederPath = database_path('seeders/DatabaseSeeder.php');

        if (! $this->filesystem->exists($seederPath)) {
            $this->error('DatabaseSeeder.php not found.');

            return false;
        }

        $content = $this->filesystem->get($seederPath);

        // Check if the dev user factory call already exists
        if (str_contains($content, 'User::factory()')) {
            $this->info('Dev user factory call already exists in DatabaseSeeder.');

            return true;
        }

        // Find the run() method body and insert the factory call
        if (preg_match('/public\s+function\s+run\(\)[^{]*\{/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPosition = $matches[0][1] + strlen($matches[0][0]);

            $factoryCall = "\n        \\App\\Models\\User::factory()->create([\n            'name' => 'Test User',\n            'email' => 'test@example.com',\n        ]);\n";

            $newContent = substr($content, 0, $insertPosition)
                .$factoryCall
                .substr($content, $insertPosition);

            if ($this->filesystem->put($seederPath, $newContent) === false) {
                $this->error('Failed to update DatabaseSeeder.php');

                return false;
            }

            $this->info('Dev user added to DatabaseSeeder successfully.');

            return true;
        }

        $this->error('Could not find run() method in DatabaseSeeder.php');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Adding dev user to DatabaseSeeder';
    }
}
