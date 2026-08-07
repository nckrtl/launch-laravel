<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\MultiLanguage;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;

class ConfigureI18nTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $viteConfigPath = base_path('vite.config.ts');

        if (! $this->filesystem->exists($viteConfigPath)) {
            $this->error('vite.config.ts not found.');

            return false;
        }

        $contents = $this->filesystem->get($viteConfigPath);

        if (preg_match('/\bi18n\s*:/', $contents) === 1) {
            $this->info('Vite i18n is already enabled.');

            return true;
        }

        $configured = str_replace(
            'defineLaunchConfig()',
            'defineLaunchConfig({ i18n: true })',
            $contents,
            $emptyConfigReplacements,
        );

        if ($emptyConfigReplacements === 0) {
            $configured = preg_replace(
                '/defineLaunchConfig\(\{\s*/',
                "defineLaunchConfig({\n    i18n: true,\n    ",
                $contents,
                1,
                $objectConfigReplacements,
            ) ?? $contents;

            if ($objectConfigReplacements === 0) {
                $this->error('Could not locate defineLaunchConfig() in vite.config.ts.');

                return false;
            }
        }

        if ($this->filesystem->put($viteConfigPath, $configured) === false) {
            $this->error('Failed to enable Vite i18n.');

            return false;
        }

        $this->info('Vite i18n enabled successfully.');

        return true;
    }

    public function description(): string
    {
        return 'Enabling i18n in the Vite configuration';
    }
}
