<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class RegisterPasskeyRoutesTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $routesPath = base_path('routes/web.php');

        if (! $this->filesystem->exists($routesPath)) {
            $this->error('routes/web.php not found.');

            return false;
        }

        $content = $this->filesystem->get($routesPath);
        $routeLine = "require base_path('vendor/laravel/passkeys/routes/routes.php');";

        if (str_contains($content, $routeLine)) {
            $this->info('Passkey routes are already registered.');

            return true;
        }

        $content = rtrim($content).PHP_EOL.PHP_EOL.$routeLine.PHP_EOL;

        if ($this->filesystem->put($routesPath, $content) === false) {
            $this->error('Failed to register passkey routes.');

            return false;
        }

        $this->info('Passkey routes registered successfully.');

        return true;
    }

    public function description(): string
    {
        return 'Registering passkey routes';
    }
}
