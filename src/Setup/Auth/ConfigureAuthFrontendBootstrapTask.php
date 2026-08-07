<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;

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
        $updated = $this->ensureSettingsLayoutImports($updated);
        $updated = $this->ensureAuthLayoutResolver($updated);
        $updated = $this->ensureSettingsLayoutResolver($updated);

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

    private function ensureSettingsLayoutImports(string $contents): string
    {
        if (! str_contains($contents, 'import AppLayout from "@/components/app-layout";')) {
            $contents = str_replace(
                'import { createInertiaApp } from "@inertiajs/react";',
                "import { createInertiaApp } from \"@inertiajs/react\";\nimport AppLayout from \"@/components/app-layout\";",
                $contents,
            );
        }

        if (! str_contains($contents, 'import SettingsLayout from "@/components/settings-layout";')) {
            $contents = str_replace(
                'import AuthLayout from "@/components/auth-layout";',
                "import AuthLayout from \"@/components/auth-layout\";\nimport SettingsLayout from \"@/components/settings-layout\";",
                $contents,
            );
        }

        return $contents;
    }

    private function ensureSettingsLayoutResolver(string $contents): string
    {
        if (str_contains($contents, 'case _name.startsWith("settings/"):')) {
            return $contents;
        }

        return str_replace(
            "case _name.startsWith(\"auth/\"):\n                return AuthLayout;",
            "case _name.startsWith(\"auth/\"):\n                return AuthLayout;\n            case _name.startsWith(\"settings/\"):\n                return [AppLayout, SettingsLayout];",
            $contents,
        );
    }
}
