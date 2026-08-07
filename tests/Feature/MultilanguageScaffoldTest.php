<?php

declare(strict_types=1);

use HardImpact\Launch\Setup\MultiLanguage\ConfigureI18nTask;
use HardImpact\Launch\Setup\MultiLanguage\CopyExamplePageTask;
use HardImpact\Launch\Setup\MultiLanguage\CopyLangDirectoryTask;
use HardImpact\Launch\Setup\SetupMultilanguage;
use HardImpact\Launch\Setup\Tasks\GenerateRoutesTask;
use Illuminate\Filesystem\Filesystem;

describe('multilanguage scaffold', function () {
    it('ships translations and an example page for the React starterkit', function () {
        $packageRoot = dirname(__DIR__, 2);

        expect("{$packageRoot}/resources/stubs/multi-language/lang/en.json")
            ->toBeFile()
            ->and("{$packageRoot}/resources/stubs/multi-language/lang/nl.json")
            ->toBeFile()
            ->and("{$packageRoot}/resources/stubs/multi-language/resources/js/pages/TranslationExample.tsx")
            ->toBeFile()
            ->and("{$packageRoot}/resources/stubs/multi-language/resources/js/pages/TranslationExample.vue")
            ->not->toBeFile();

        expect(file_get_contents("{$packageRoot}/resources/stubs/multi-language/resources/js/pages/TranslationExample.tsx"))
            ->toContain('@hardimpactdev/launch-ui/i18n')
            ->toContain('setLocale')
            ->toContain('useLocale');
    });

    it('configures i18n before generating routes', function () {
        $setup = new SetupMultilanguage(new Filesystem);
        $tasks = new ReflectionProperty($setup, 'tasks');

        expect(class_exists(ConfigureI18nTask::class))->toBeTrue()
            ->and($tasks->getValue($setup))->toBe([
                CopyLangDirectoryTask::class,
                CopyExamplePageTask::class,
                ConfigureI18nTask::class,
                GenerateRoutesTask::class,
            ]);
    });

    it('enables i18n in an empty Launch Vite configuration', function () {
        $filesystem = new Filesystem;
        $originalBasePath = app()->basePath();
        $temporaryBasePath = sys_get_temp_dir().'/launch-i18n-'.uniqid();
        $viteConfig = <<<'TYPESCRIPT'
import { defineLaunchConfig } from "@hardimpactdev/launch-ui/vite";

export default await defineLaunchConfig();
TYPESCRIPT;

        $filesystem->ensureDirectoryExists($temporaryBasePath);
        $filesystem->put("{$temporaryBasePath}/vite.config.ts", $viteConfig);
        app()->setBasePath($temporaryBasePath);

        try {
            $task = new ConfigureI18nTask($filesystem);

            expect($task->run())->toBeTrue()
                ->and($filesystem->get("{$temporaryBasePath}/vite.config.ts"))
                ->toContain('defineLaunchConfig({ i18n: true })');
        } finally {
            app()->setBasePath($originalBasePath);
            $filesystem->deleteDirectory($temporaryBasePath);
        }
    });

    it('adds i18n to an existing Launch Vite configuration once', function () {
        $filesystem = new Filesystem;
        $originalBasePath = app()->basePath();
        $temporaryBasePath = sys_get_temp_dir().'/launch-i18n-'.uniqid();
        $viteConfig = <<<'TYPESCRIPT'
import { defineLaunchConfig } from "@hardimpactdev/launch-ui/vite";

export default await defineLaunchConfig({
    wayfinder: {
        formVariants: true,
    },
});
TYPESCRIPT;

        $filesystem->ensureDirectoryExists($temporaryBasePath);
        $filesystem->put("{$temporaryBasePath}/vite.config.ts", $viteConfig);
        app()->setBasePath($temporaryBasePath);

        try {
            $task = new ConfigureI18nTask($filesystem);

            expect($task->run())->toBeTrue()
                ->and($task->run())->toBeTrue();

            $configured = $filesystem->get("{$temporaryBasePath}/vite.config.ts");

            expect($configured)
                ->toContain("defineLaunchConfig({\n    i18n: true,")
                ->and(substr_count($configured, 'i18n: true'))
                ->toBe(1);
        } finally {
            app()->setBasePath($originalBasePath);
            $filesystem->deleteDirectory($temporaryBasePath);
        }
    });
});
