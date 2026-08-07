<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Cms;

use HardImpact\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class RegisterFilamentServiceProviderTask extends Task
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
        $providersPath = base_path('bootstrap/providers.php');

        if (! $this->filesystem->exists($providersPath)) {
            $this->error('bootstrap/providers.php file not found');

            return false;
        }

        $providersContent = $this->filesystem->get($providersPath);
        $providerClass = 'App\\Providers\\Filament\\AdminPanelProvider::class';

        // Check if the provider is already registered
        if (str_contains($providersContent, $providerClass)) {
            $this->info('FilamentServiceProvider is already registered.');

            return true;
        }

        // Find the position to insert the new provider
        $pattern = '/return\s*\[\s*(.*?)\s*\];/s';

        if (! preg_match($pattern, $providersContent, $matches)) {
            $this->error('Could not parse providers.php file');

            return false;
        }

        $providers = $matches[1];

        // Add the new provider to the list
        $newProviders = rtrim($providers, ',').",\n    ".$providerClass.',';

        $newContent = preg_replace(
            $pattern,
            "return [\n    ".$newProviders."\n];",
            $providersContent
        );

        if ($this->filesystem->put($providersPath, $newContent) === false) {
            $this->error('Failed to update providers.php');

            return false;
        }

        $this->info('FilamentServiceProvider registered successfully in bootstrap/providers.php');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Registering FilamentServiceProvider';
    }
}
