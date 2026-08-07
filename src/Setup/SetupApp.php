<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup;

use HardImpact\Craft\Setup\App\CopyAppClassTask;
use HardImpact\Craft\Setup\App\CopyAppControllersTask;
use HardImpact\Craft\Setup\App\CopyAppMiddlewareTask;
use HardImpact\Craft\Setup\App\CopyAppRequestsTask;
use HardImpact\Craft\Setup\App\CopyAppTestsTask;
use HardImpact\Craft\Setup\App\CopyFrontendBootstrapTask;
use HardImpact\Craft\Setup\App\InstallAppReactScaffoldTask;
use HardImpact\Craft\Setup\App\RunSetupAuthTask;
use HardImpact\Craft\Setup\Tasks\GenerateRoutesTask;
use Illuminate\Filesystem\Filesystem;

class SetupApp extends Setup
{
    /**
     * The tasks to run.
     *
     * Sets up a full application with authentication, dashboard, and settings.
     * Does NOT include Filament - run `craft:setup filament` separately if needed.
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
