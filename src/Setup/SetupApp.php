<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup;

use HardImpact\Launch\Setup\App\CopyAppClassTask;
use HardImpact\Launch\Setup\App\CopyAppControllersTask;
use HardImpact\Launch\Setup\App\CopyAppMiddlewareTask;
use HardImpact\Launch\Setup\App\CopyAppRequestsTask;
use HardImpact\Launch\Setup\App\CopyAppTestsTask;
use HardImpact\Launch\Setup\App\CopyFrontendBootstrapTask;
use HardImpact\Launch\Setup\App\InstallAppReactScaffoldTask;
use HardImpact\Launch\Setup\App\RunSetupAuthTask;
use HardImpact\Launch\Setup\Tasks\GenerateRoutesTask;
use Illuminate\Filesystem\Filesystem;

class SetupApp extends Setup
{
    /**
     * The tasks to run.
     *
     * Sets up a full application with authentication, dashboard, and settings.
     * Does NOT include Filament - run `launch:setup filament` separately if needed.
     *
     * @var array
     */
    protected $tasks = [
        RunSetupAuthTask::class,
        CopyAppClassTask::class,
        CopyAppControllersTask::class,
        CopyAppMiddlewareTask::class,
        CopyAppRequestsTask::class,
        InstallAppReactScaffoldTask::class,
        CopyFrontendBootstrapTask::class,
        CopyAppTestsTask::class,
        GenerateRoutesTask::class,
    ];

    /**
     * Create a new setup instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct($filesystem);
    }
}
