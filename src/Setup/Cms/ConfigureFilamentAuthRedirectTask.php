<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Cms;

use HardImpact\Craft\Setup\Tasks\Task;

class ConfigureFilamentAuthRedirectTask extends Task
{
    public function run(): bool
    {
        $configPath = config_path('fortify.php');

        if (! $this->filesystem->exists($configPath)) {
            $this->error('config/fortify.php not found.');

            return false;
        }

        $contents = $this->filesystem->get($configPath);
        $updated = $this->ensureAdminPath($contents);
        $updated = $this->ensureFilamentHomePath($updated);

        if ($updated === $contents) {
            $this->info('Filament auth redirect already configured.');

            return $this->updateGeneratedRedirects();
        }

        $this->filesystem->put($configPath, $updated);
        $this->info('Filament auth redirect configured.');

        return $this->updateGeneratedRedirects();
    }

    private function updateGeneratedRedirects(): bool
    {
        if (! $this->usesFilamentHomePath()) {
            return true;
        }

        $this->replaceInFileIfExists(
            config_path('passkeys.php'),
            "'redirect' => '/dashboard',",
            "'redirect' => '/admin',",
        );

        $this->replaceInFileIfExists(
            base_path('tests/Feature/Auth/AuthenticationTest.php'),
            "\$response->assertRedirect('/dashboard');",
            "\$response->assertRedirect('/admin');",
        );

        return true;
    }

    public function description(): string
    {
        return 'Configuring Filament auth redirect';
    }

    private function ensureAdminPath(string $contents): string
    {
        if (str_contains($contents, "'admin',") || str_contains($contents, '"admin",')) {
            return $contents;
        }

        return str_replace(
            "'non_inertia_paths' => [\n        //\n    ],",
            "'non_inertia_paths' => [\n        'admin',\n    ],",
            $contents,
        );
    }

    private function ensureFilamentHomePath(string $contents): string
    {
        if (! $this->usesFilamentHomePath()) {
            return $contents;
        }

        return str_replace(
            "'home' => '/dashboard',",
            "'home' => '/admin',",
            $contents,
        );
    }

    private function usesFilamentHomePath(): bool
    {
        return ! $this->filesystem->exists(app_path('Http/Controllers/DashboardController.php'));
    }

    private function replaceInFileIfExists(string $path, string $search, string $replace): void
    {
        if (! $this->filesystem->exists($path)) {
            return;
        }

        $contents = $this->filesystem->get($path);
        $updated = str_replace($search, $replace, $contents);

        if ($updated === $contents) {
            return;
        }

        $this->filesystem->put($path, $updated);
    }
}
