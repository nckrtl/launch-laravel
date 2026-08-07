<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ConfigurePasskeysTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');

        if (! $this->filesystem->exists($providerPath)) {
            $this->error('AppServiceProvider.php not found.');

            return false;
        }

        $content = $this->filesystem->get($providerPath);
        $content = $this->ensureUseStatement(
            $content,
            app()->getNamespace().'Http\\Middleware\\RequireSensitiveActionConfirmation'
        );

        if (! str_contains($content, "'passkeys.management_middleware'")) {
            $content = preg_replace(
                '/public function boot\(\): void\s*\{/',
                "public function boot(): void\n    {\n        config([\n            'passkeys.management_middleware' => [RequireSensitiveActionConfirmation::class],\n        ]);\n",
                $content,
                1
            ) ?? $content;
        }

        if ($this->filesystem->put($providerPath, $content) === false) {
            $this->error('Failed to configure passkeys.');

            return false;
        }

        $this->info('Passkeys configured successfully.');

        return true;
    }

    public function description(): string
    {
        return 'Configuring passkeys';
    }

    private function ensureUseStatement(string $content, string $class): string
    {
        if (str_contains($content, "use {$class};")) {
            return $content;
        }

        if (preg_match('/^use [^;]+;/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPosition = $matches[0][1];

            return substr($content, 0, $insertPosition)."use {$class};\n".substr($content, $insertPosition);
        }

        return $content;
    }
}
