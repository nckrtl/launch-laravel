<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use NckRtl\Launch\Setup\App\CopyAppControllersTask;
use NckRtl\Launch\Setup\App\RegisterAppRoutesTask;
use NckRtl\Launch\Setup\SetupApp;

describe('app scaffold', function () {
    it('declares routes in route files instead of controller attributes', function () {
        $packageRoot = dirname(__DIR__, 2);
        $controllers = array_filter(
            (new Filesystem)->allFiles($packageRoot.'/resources/stubs'),
            fn (SplFileInfo $file): bool => str_contains($file->getPathname(), 'Http/Controllers/'),
        );

        expect($controllers)->not->toBeEmpty();

        foreach ($controllers as $file) {
            expect($file->getContents())
                ->not->toContain('#[')
                ->not->toContain('$routePrefix');
        }

        expect("{$packageRoot}/resources/stubs/app/routes/settings.php")->toBeFile()
            ->and("{$packageRoot}/src/Setup/Tasks/GenerateRoutesTask.php")->not->toBeFile();
    });

    it('ships a Vite configuration without Agentation or artisan runners', function () {
        $viteConfig = file_get_contents(dirname(__DIR__, 2).'/resources/stubs/app/vite.config.ts');

        expect($viteConfig)
            ->toContain('defineLaunchConfig(')
            ->not->toContain('agentation')
            ->not->toContain('artisan');
    });

    it('registers app routes after copying the controllers', function () {
        $tasks = (new ReflectionProperty(SetupApp::class, 'tasks'))
            ->getValue(new SetupApp(new Filesystem));

        expect($tasks)->toContain(RegisterAppRoutesTask::class)
            ->and(array_search(RegisterAppRoutesTask::class, $tasks, true))
            ->toBeGreaterThan(array_search(CopyAppControllersTask::class, $tasks, true));
    });

    it('adds the dashboard and settings routes to an existing routes file', function () {
        $filesystem = new Filesystem;
        $namespace = app()->getNamespace();
        expect($namespace)->toBe('App\\');
        $originalBasePath = app()->basePath();
        $temporaryBasePath = sys_get_temp_dir().'/launch-routes-'.uniqid();
        $webRoutes = <<<'PHP'
<?php

declare(strict_types=1);

use App\Http\Controllers\AgentController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])->name('home');
Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');

PHP;

        $filesystem->ensureDirectoryExists("{$temporaryBasePath}/routes");
        $filesystem->put("{$temporaryBasePath}/routes/web.php", $webRoutes);
        app()->setBasePath($temporaryBasePath);

        try {
            $task = new RegisterAppRoutesTask($filesystem);

            expect($task->run())->toBeTrue();
            $firstRun = $filesystem->get("{$temporaryBasePath}/routes/web.php");

            expect($task->run())->toBeTrue()
                ->and($filesystem->get("{$temporaryBasePath}/routes/web.php"))->toBe($firstRun);

            expect($firstRun)
                ->toContain(implode(PHP_EOL, [
                    'use App\\Http\\Controllers\\AgentController;',
                    'use App\\Http\\Controllers\\DashboardController;',
                    'use App\\Http\\Controllers\\HomeController;',
                    'use Illuminate\\Support\\Facades\\Route;',
                ]))
                ->toContain("Route::get('/', [HomeController::class, 'show'])->name('home');")
                ->toContain("Route::get('dashboard', [DashboardController::class, 'show'])->middleware('auth')->name('dashboard');")
                ->toContain("require __DIR__.'/settings.php';");

            expect($filesystem->get("{$temporaryBasePath}/routes/settings.php"))
                ->toContain("use {$namespace}Http\\Controllers\\Settings\\ProfileController;")
                ->not->toContain('{{namespace}}');

            foreach (['web.php', 'settings.php'] as $file) {
                exec('php -l '.escapeshellarg("{$temporaryBasePath}/routes/{$file}").' 2>&1', $output, $exitCode);
                expect($exitCode)->toBe(0, implode(PHP_EOL, $output));
            }

            Route::middleware('web')->group("{$temporaryBasePath}/routes/web.php");
            $routes = Route::getRoutes();
            $routes->refreshNameLookups();

            $expected = [
                'dashboard' => ['GET', 'dashboard', 'DashboardController@show', ['web', 'auth']],
                'settings.profile.edit' => ['GET', 'settings/profile', 'Settings\\ProfileController@edit', ['web', 'auth']],
                'settings.profile.update' => ['PATCH', 'settings/profile', 'Settings\\ProfileController@update', ['web', 'auth']],
                'settings.profile.destroy' => ['DELETE', 'settings/profile', 'Settings\\ProfileController@destroy', ['web', 'auth']],
                'settings.security.edit' => ['GET', 'settings/security', 'Settings\\SecurityController@edit', ['web', 'auth', 'verified']],
                'settings.security.update' => ['PUT', 'settings/security', 'Settings\\SecurityController@update', ['web', 'auth']],
                'settings.password.edit' => ['GET', 'settings/password', 'Settings\\PasswordController@edit', ['web', 'auth']],
                'settings.password.update' => ['PUT', 'settings/password', 'Settings\\PasswordController@update', ['web', 'auth']],
                'settings.appearance.edit' => ['GET', 'settings/appearance', 'Settings\\AppearanceController@edit', ['web', 'auth']],
                'two-factor.show' => ['GET', 'settings/two-factor', 'Settings\\TwoFactorAuthenticationController@show', ['web', 'auth', 'verified']],
            ];

            foreach ($expected as $name => [$method, $uri, $action, $middleware]) {
                $route = $routes->getByName($name);

                expect($route)->not->toBeNull("Route [{$name}] is not registered.")
                    ->and($route->methods()[0])->toBe($method)
                    ->and($route->uri())->toBe($uri)
                    ->and($route->getActionName())->toBe("{$namespace}Http\\Controllers\\{$action}")
                    ->and($route->middleware())->toBe($middleware);
            }
        } finally {
            app()->setBasePath($originalBasePath);
            $filesystem->deleteDirectory($temporaryBasePath);
        }
    });

    it('fails when the application has no web routes file', function () {
        $filesystem = new Filesystem;
        $originalBasePath = app()->basePath();
        $temporaryBasePath = sys_get_temp_dir().'/launch-routes-'.uniqid();

        $filesystem->ensureDirectoryExists($temporaryBasePath);
        app()->setBasePath($temporaryBasePath);

        try {
            expect((new RegisterAppRoutesTask($filesystem))->run())->toBeFalse()
                ->and("{$temporaryBasePath}/routes/settings.php")->not->toBeFile();
        } finally {
            app()->setBasePath($originalBasePath);
            $filesystem->deleteDirectory($temporaryBasePath);
        }
    });
});
