<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\App;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;

class RegisterAppRoutesTask extends Task
{
    private const SETTINGS_REQUIRE = "require __DIR__.'/settings.php';";

    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $webRoutesPath = base_path('routes/web.php');

        if (! $this->filesystem->exists($webRoutesPath)) {
            $this->error('routes/web.php not found.');

            return false;
        }

        $namespace = app()->getNamespace();

        if (! $this->copyFile(
            __DIR__.'/../../../resources/stubs/app/routes/settings.php',
            base_path('routes/settings.php'),
            ['{{namespace}}' => $namespace],
        )) {
            $this->error('Failed to copy routes/settings.php.');

            return false;
        }

        $contents = $this->filesystem->get($webRoutesPath);
        $updated = $this->registerWebRoutes($contents, $namespace);

        if ($updated !== $contents && $this->filesystem->put($webRoutesPath, $updated) === false) {
            $this->error('Failed to register app routes in routes/web.php.');

            return false;
        }

        $this->info('App routes registered.');

        return true;
    }

    public function description(): string
    {
        return 'Registering app routes';
    }

    private function registerWebRoutes(string $contents, string $namespace): string
    {
        $append = [];

        if (! str_contains($contents, 'DashboardController::class')) {
            $contents = $this->addImports($contents, [
                $namespace.'Http\\Controllers\\DashboardController',
                'Illuminate\\Support\\Facades\\Route',
            ]);

            $append[] = "Route::get('dashboard', [DashboardController::class, 'show'])->middleware('auth')->name('dashboard');";
        }

        if (! str_contains($contents, self::SETTINGS_REQUIRE)) {
            $append[] = self::SETTINGS_REQUIRE;
        }

        if ($append === []) {
            return $contents;
        }

        return rtrim($contents).PHP_EOL.PHP_EOL.implode(PHP_EOL.PHP_EOL, $append).PHP_EOL;
    }

    /**
     * Add class imports to the top-level import block, keeping it sorted.
     *
     * @param  array<int, string>  $classes
     */
    private function addImports(string $contents, array $classes): string
    {
        $lines = preg_split('/\R/', $contents) ?: [];
        $imports = [];
        $first = null;
        $last = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^use\s+([^;]+);\s*$/', $line, $matches) === 1) {
                $imports[] = trim($matches[1]);
                $first ??= $index;
                $last = $index;

                continue;
            }

            if ($first !== null && trim($line) !== '') {
                break;
            }
        }

        $missing = array_values(array_diff($classes, $imports));

        if ($missing === []) {
            return $contents;
        }

        $imports = array_merge($imports, $missing);
        usort($imports, fn (string $a, string $b): int => strcasecmp(
            str_replace('\\', ' ', $a),
            str_replace('\\', ' ', $b),
        ));
        $block = array_map(fn (string $import): string => "use {$import};", $imports);

        if ($first !== null && $last !== null) {
            array_splice($lines, $first, $last - $first + 1, $block);

            return implode(PHP_EOL, $lines);
        }

        $insertAt = 0;

        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), 'declare(') || trim($line) === '<?php') {
                $insertAt = $index + 1;
            }
        }

        array_splice($lines, $insertAt, 0, ['', ...$block]);

        return implode(PHP_EOL, $lines);
    }
}
