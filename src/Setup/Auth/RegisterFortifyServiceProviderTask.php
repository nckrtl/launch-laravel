<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;

class RegisterFortifyServiceProviderTask extends Task
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
        $bootstrapPath = base_path('bootstrap/providers.php');

        if (! $this->filesystem->exists($bootstrapPath)) {
            $this->error('bootstrap/providers.php not found.');

            return false;
        }

        $content = $this->filesystem->get($bootstrapPath);
        $providerClass = 'App\\Providers\\FortifyServiceProvider::class';
        $content = $this->ensurePasskeysProvider($content);

        // Check if provider is already registered
        if (str_contains($content, $providerClass)) {
            $this->filesystem->put($bootstrapPath, $content);
            $this->info('FortifyServiceProvider is already registered.');

            return true;
        }

        // Find the return statement with the array
        if (preg_match('/return\s*\[\s*/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPosition = $matches[0][1] + strlen($matches[0][0]);

            // Insert the provider at the beginning of the array
            $newContent = substr($content, 0, $insertPosition)
                .$providerClass.",\n    "
                .substr($content, $insertPosition);

            if ($this->filesystem->put($bootstrapPath, $newContent) === false) {
                $this->error('Failed to update bootstrap/providers.php');

                return false;
            }

            $this->info('FortifyServiceProvider registered successfully.');

            return true;
        }

        $this->error('Could not find providers array in bootstrap/providers.php');

        return false;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Registering FortifyServiceProvider';
    }

    private function ensurePasskeysProvider(string $content): string
    {
        if (! str_contains($content, 'use Laravel\\Passkeys\\PasskeysServiceProvider;')) {
            $content = str_replace(
                "use App\\Providers\\ToolbarConfigProvider;\n",
                "use App\\Providers\\ToolbarConfigProvider;\nuse Laravel\\Passkeys\\PasskeysServiceProvider;\n",
                $content
            );
        }

        if (! str_contains($content, 'PasskeysServiceProvider::class')) {
            $content = str_replace(
                "    AppServiceProvider::class,\n",
                "    AppServiceProvider::class,\n    PasskeysServiceProvider::class,\n",
                $content
            );
        }

        return $content;
    }
}
