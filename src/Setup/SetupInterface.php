<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup;

use Illuminate\Console\Command;

interface SetupInterface
{
    /**
     * Run the setup process.
     *
     * @return int Exit code (0 for success, non-zero for failure)
     */
    public function setup(): int;

    /**
     * Set the command instance.
     *
     * @return $this
     */
    public function setCommand(Command $command): static;
}
