<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Auth;

use HardImpact\Launch\Setup\Tasks\Task;

class ConfigureAuthFrontendBootstrapTask extends Task
{
    public function run(): bool
    {
        $appPath = resource_path('js/app.tsx');

        if (! $this->filesystem->exists($appPath)) {
            $this->error('resources/js/app.tsx not found.');

            return false;
        }

        $contents = $this->filesystem->get($appPath);
        $updated = $this->ensureAuthLayoutImport($contents);
        $updated = $this->ensureAuthLayoutResolver($updated);

        if ($updated === $contents) {
            $this->info('Auth frontend bootstrap already configured.');

            return true;
        }

        $this->filesystem->put($appPath, $updated);
        $this->info('Auth frontend bootstrap configured.');

        return true;
    }

    public function description(): string
    {
        return 'Configuring auth frontend bootstrap';
    }

    private function ensureAuthLayoutImport(string $contents): string
    {
        if (str_contains($contents, 'import AuthLayout from "@/components/auth-layout";')) {
            return $contents;
        }

        return str_replace(
            'import { createInertiaApp } from "@inertiajs/react";',
            "import { createInertiaApp } from \"@inertiajs/react\";\nimport AuthLayout from \"@/components/auth-layout\";",
            $contents,
        );
    }

    private function ensureAuthLayoutResolver(string $contents): string
    {
        if (str_contains($contents, 'case _name.startsWith("auth/"):')) {
            return $contents;
        }

        return str_replace(
            'switch (true) {',
            "switch (true) {\n            case _name.startsWith(\"auth/\"):\n                return AuthLayout;",
            $contents,
        );
    }
}
