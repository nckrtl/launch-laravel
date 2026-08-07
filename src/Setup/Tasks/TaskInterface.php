<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Tasks;

interface TaskInterface
{
    /**
     * Run the task.
     *
     * @return bool Success status
     */
    public function run(): bool;

    /**
     * Get the task description.
     */
    public function description(): string;
}
