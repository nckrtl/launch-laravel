<?php

declare(strict_types=1);

use HardImpact\Craft\Commands\SetupCommand;
use HardImpact\Craft\LaravelServiceProvider;
use HardImpact\Craft\Setup\SetupApp;
use HardImpact\Craft\Setup\SetupFilament;
use HardImpact\Craft\Setup\SetupMultilanguage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

describe('package service provider', function () {
    it('boots on Laravel 13 and registers its command', function () {
        expect(str_starts_with(app()->version(), '13.'))->toBeTrue();
        expect(config('laravel.defaults.strict_models'))->toBeTrue();
        expect(Artisan::all())
            ->toHaveKey('craft:setup')
            ->and(Artisan::all()['craft:setup'])
            ->toBeInstanceOf(SetupCommand::class);
    });

    it('runs the setup command missing setup path without crashing', function () {
        $this
            ->artisan('craft:setup missing')
            ->expectsOutput("Setup for 'missing' not found.")
            ->assertExitCode(1);
    });

    it('only resolves the supported setup commands', function () {
        $command = new SetupCommand(app(Filesystem::class));
        $method = new ReflectionMethod($command, 'resolveSetup');

        $resolve = fn (string $type): ?object => $method->invoke($command, $type);

        expect($resolve('app'))->toBeInstanceOf(SetupApp::class)
            ->and($resolve('filament'))->toBeInstanceOf(SetupFilament::class)
            ->and($resolve('multilanguage'))->toBeInstanceOf(SetupMultilanguage::class);

        foreach (['auth', 'dashboard', 'cms', 'task-tracking', 'missing'] as $type) {
            expect($resolve($type))->toBeNull();
        }
    });

    it('allows Inertia SSR requests while preventing other stray HTTP requests', function () {
        config()->set('inertia.ssr.enabled', true);
        config()->set('inertia.ssr.url', 'http://127.0.0.1:13714/');

        $hotFile = tempnam(sys_get_temp_dir(), 'craft-vite-hot-');

        expect($hotFile)->not->toBeFalse();

        file_put_contents($hotFile, 'https://hauser.test:5173');

        app(Vite::class)->useHotFile($hotFile);

        try {
            (new LaravelServiceProvider(app()))->packageBooted();

            expect(Http::preventingStrayRequests())->toBeTrue();
            expect(Http::isAllowedRequestUrl('http://127.0.0.1:13714/render'))->toBeTrue();
            expect(Http::isAllowedRequestUrl('http://127.0.0.1:13714/health'))->toBeTrue();
            expect(Http::isAllowedRequestUrl('https://hauser.test:5173/__inertia_ssr'))->toBeTrue();
            expect(Http::isAllowedRequestUrl('https://example.com'))->toBeFalse();
        } finally {
            app(Vite::class)->useHotFile(public_path('hot'));
            unlink($hotFile);
        }
    });
});
