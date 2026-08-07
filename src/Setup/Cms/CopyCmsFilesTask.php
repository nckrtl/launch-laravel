<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Cms;

use HardImpact\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyCmsFilesTask extends Task
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
        $stubBasePath = __DIR__.'/../../../resources/stubs/cms';

        // Define the mapping of stub directories to destination directories
        $mappings = [
            'app/Filament' => app_path('Filament'),
            'app/Http/Middleware' => app_path('Http/Middleware'),
            'app/Resources' => app_path('Filament/Resources'),
            'resources/css' => resource_path('css'),
            'resources/views' => resource_path('views'),
        ];

        $replacements = [
            '{{namespace}}' => app()->getNamespace(),
        ];

        foreach ($mappings as $stubDir => $destinationDir) {
            $fromPath = $stubBasePath.'/'.$stubDir;

            if (! $this->filesystem->exists($fromPath)) {
                $this->info("Skipping {$stubDir} - path does not exist");

                continue;
            }

            if (! $this->copyDirectory($fromPath, $destinationDir, $replacements)) {
                $this->error("Failed to copy {$stubDir} to {$destinationDir}");

                return false;
            }

            $this->info("Copied {$stubDir} successfully");
        }

        // Move AdminPanelProvider to the correct location
        $adminPanelProviderSource = app_path('Filament/AdminPanelProvider.php');
        $adminPanelProviderDest = app_path('Providers/Filament/AdminPanelProvider.php');

        if ($this->filesystem->exists($adminPanelProviderSource)) {
            $this->filesystem->ensureDirectoryExists(app_path('Providers/Filament'));

            if (! $this->filesystem->move($adminPanelProviderSource, $adminPanelProviderDest)) {
                $this->error('Failed to move AdminPanelProvider to Providers directory');

                return false;
            }

            $this->info('Moved AdminPanelProvider to app/Providers/Filament/');
        }

        $this->info('CMS files copied successfully.');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying CMS stub files';
    }
}
