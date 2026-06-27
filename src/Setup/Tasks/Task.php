<?php

namespace HardImpact\Craft\Setup\Tasks;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class Task implements TaskInterface
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The command instance.
     *
     * @var Command|null
     */
    protected $command;

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
     *
     * @param  string  $message
     * @return void
     */
    protected function info($message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * Output an error message.
     *
     * @param  string  $message
     * @return void
     */
    protected function error($message)
    {
        if ($this->command) {
            $this->command->error($message);
        }
    }

    /**
     * Copy a file, replacing placeholders.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    protected function copyFile($from, $to, array $replacements = [])
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
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    protected function copyDirectory($from, $to, array $replacements = [])
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
