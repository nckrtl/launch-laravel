<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Tasks;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class InstallCraftReactRegistryItemsTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        $componentsPath = base_path('components.json');
        $registryPath = $this->registryPath();

        if (! $this->filesystem->isDirectory($registryPath)) {
            $this->error("Craft UI React registry not found. Checked {$registryPath}. Run `vp link /home/nckrtl/projects/craft-ui-react` or `npm install` after building the Craft UI React registry.");

            return false;
        }

        if (! $this->filesystem->exists($componentsPath)) {
            $this->error('components.json not found. Initialize shadcn before running this scaffold.');

            return false;
        }

        $originalComponentsJson = $this->filesystem->get($componentsPath);
        $port = $this->availablePort();
        $server = $this->registryServerProcess($registryPath, $port);
        $server->disableOutput();
        $server->setTimeout(null);
        $server->start();

        try {
            if (! $this->waitForRegistry($port)) {
                $this->error('Failed to start local Craft UI React registry server.');

                return false;
            }

            $this->pointCraftRegistryAtLocalServer($componentsPath, $port);

            $process = new Process([
                'npx',
                'shadcn',
                'add',
                '--yes',
                '--overwrite',
                ...$this->items(),
            ], base_path());
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error('Failed to install Craft React scaffold: '.$process->getErrorOutput());

                return false;
            }

            $this->normalizeGeneratedImports(base_path());

            if (! $this->formatGeneratedFiles()) {
                return false;
            }

            $this->info('Craft React scaffold installed successfully.');

            return true;
        } finally {
            $this->filesystem->put($componentsPath, $originalComponentsJson);
            $server->stop();
        }
    }

    /**
     * @return list<string>
     */
    abstract protected function items(): array;

    private function registryPath(): string
    {
        $paths = [
            base_path('node_modules/@hardimpactdev/craft-ui-react/public/r'),
            '/home/nckrtl/projects/craft-ui-react/public/r',
        ];

        foreach ($paths as $path) {
            if ($this->filesystem->isDirectory($path)) {
                return $path;
            }
        }

        return $paths[0];
    }

    private function availablePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');

        if (! is_resource($socket)) {
            return random_int(41000, 49000);
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr((string) $name, strrpos((string) $name, ':') + 1);
    }

    private function registryServerProcess(string $registryPath, int $port): Process
    {
        return new Process(
            ['php', '-S', "127.0.0.1:{$port}", '-t', $registryPath],
            base_path(),
            ['PHP_CLI_SERVER_WORKERS' => '8'],
        );
    }

    private function formatterProcess(): Process
    {
        return new Process(['vp', 'check', '--fix', 'resources/js'], base_path());
    }

    private function formatGeneratedFiles(): bool
    {
        if (! $this->filesystem->isDirectory(base_path('resources/js'))) {
            return true;
        }

        $process = $this->formatterProcess();
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Failed to format generated React scaffold: '.$process->getErrorOutput());

            return false;
        }

        return true;
    }

    private function waitForRegistry(int $port): bool
    {
        $url = "http://127.0.0.1:{$port}/registry.json";

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $contents = @file_get_contents($url);

            if (is_string($contents) && str_contains($contents, '"name"')) {
                return true;
            }

            usleep(100_000);
        }

        return false;
    }

    private function pointCraftRegistryAtLocalServer(string $componentsPath, int $port): void
    {
        $components = json_decode($this->filesystem->get($componentsPath), true, flags: JSON_THROW_ON_ERROR);
        $components['aliases'] ??= [];
        $components['aliases']['routes'] ??= '@/routes';
        $components['aliases']['pages'] ??= '@/pages';
        $components['registries'] ??= [];
        $components['registries']['@craft'] = "http://127.0.0.1:{$port}/{name}.json";

        $this->filesystem->put(
            $componentsPath,
            json_encode($components, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );
    }

    private function normalizeGeneratedImports(string $basePath): void
    {
        $jsPath = "{$basePath}/resources/js";

        if (! $this->filesystem->isDirectory($jsPath)) {
            return;
        }

        foreach ($this->filesystem->allFiles($jsPath) as $file) {
            if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
                continue;
            }

            $path = $file->getPathname();
            $contents = $this->filesystem->get($path);
            $normalized = str_replace(
                [
                    '@/resources/js/pages/auth/login',
                    '@/resources/js/pages/auth/register',
                    '@/resources/js/pages/auth/two-factor-challenge',
                    '@/resources/js/actions',
                    '@/resources/js/routes',
                    '@/resources/js/pages',
                    '@/pages/auth/login',
                    '@/pages/auth/register',
                    '@/pages/auth/two-factor-challenge',
                ],
                [
                    '@/routes/login',
                    '@/routes/register',
                    '@/routes/two-factor/login',
                    '@/actions',
                    '@/routes',
                    '@/pages',
                    '@/routes/login',
                    '@/routes/register',
                    '@/routes/two-factor/login',
                ],
                $contents
            );

            if (str_ends_with($path, 'resources/js/pages/auth/two-factor-challenge.tsx')) {
                $normalized = str_replace('@/routes/login', '@/routes/two-factor/login', $normalized);
            }

            if ($normalized !== $contents) {
                $this->filesystem->put($path, $normalized);
            }
        }
    }
}
