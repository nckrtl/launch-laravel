<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use NckRtl\Launch\Commands\SetupCommand;
use NckRtl\Launch\LaravelServiceProvider;
use NckRtl\Launch\Setup\SetupApp;
use NckRtl\Launch\Setup\SetupFilament;
use NckRtl\Launch\Setup\SetupMultilanguage;

describe('package service provider', function () {
    it('boots on supported Laravel versions and registers its setup command', function () {
        expect(app()->version())->toMatch('/^(12|13)\./');
        expect(config('launch-laravel.defaults.strict_models'))->toBeTrue();
        expect(Artisan::all())
            ->toHaveKey('launch:setup')
            ->and(Artisan::all()['launch:setup'])
            ->toBeInstanceOf(SetupCommand::class)
            ->and(Artisan::all())
            ->not->toHaveKey('craft');
    });

    it('runs the setup command missing setup path without crashing', function () {
        $this
            ->artisan('launch:setup missing')
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

        $hotFile = tempnam(sys_get_temp_dir(), 'launch-vite-hot-');

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
