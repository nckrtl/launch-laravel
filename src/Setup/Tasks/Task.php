<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Tasks;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class Task implements TaskInterface
{
    protected Filesystem $filesystem;

    protected ?Command $command;

    /**
     * Create a new task instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        $this->filesystem = $filesystem;
        $this->command = $command;
    }

    /**
     * Output an info message.
     */
    protected function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * Output an error message.
     */
    protected function error(string $message): void
    {
        if ($this->command) {
            $this->command->error($message);
        }
    }

    /**
     * Copy a file, replacing placeholders.
     */
    protected function copyFile(string $from, string $to, array $replacements = []): bool
    {
        if (! $this->filesystem->exists($from)) {
            $this->error("Source file not found: {$from}");

            return false;
        }

        $this->filesystem->ensureDirectoryExists(dirname($to));

        $contents = $this->filesystem->get($from);

        foreach ($replacements as $search => $replace) {
            $contents = str_replace($search, $replace, $contents);
        }

        return $this->filesystem->put($to, $contents) !== false;
    }

    /**
     * Copy a directory recursively.
     */
    protected function copyDirectory(string $from, string $to, array $replacements = []): bool
    {
        if (! $this->filesystem->isDirectory($from)) {
            $this->error("Source directory not found: {$from}");

            return false;
        }

        $this->filesystem->ensureDirectoryExists($to);

        $files = $this->filesystem->allFiles($from);

        foreach ($files as $file) {
            $fromPath = $file->getPathname();
            $relativePath = $file->getRelativePathname();
            $toPath = $to.'/'.$relativePath;

            if (! $this->copyFile($fromPath, $toPath, $replacements)) {
                return false;
            }
        }

        return true;
    }
}
