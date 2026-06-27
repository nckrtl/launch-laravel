<?php

namespace HardImpact\Craft\Setup;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class Setup implements SetupInterface
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
     * The tasks to run.
     *
     * @var array
     */
    protected $tasks = [];

    /**
     * Create a new setup instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Set the command instance.
     *
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Run the setup process.
     */
    public function setup(): int
    {
        if ($this->command) {
            $this->command->info('Starting setup...');
        }

        foreach ($this->tasks as $taskClass) {
            // Make sure we're passing the right dependencies
            $task = app()->makeWith($taskClass, [
                'filesystem' => $this->filesystem,
                'command' => $this->command,
            ]);

            if ($this->command) {
                $this->command->info("Task: {$task->description()}");
            }

            if (! $task->run()) {
                if ($this->command) {
                    $this->command->error("Task failed: {$task->description()}");
                }

                return 1;
            }
        }

        if ($this->command) {
            $this->command->info('Setup completed successfully!');
        }

        return 0;
    }

    protected function info($message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    protected function error($message)
    {
        if ($this->command) {
            $this->command->error($message);
        }
    }
}
