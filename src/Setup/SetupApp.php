<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup;

use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\App\CopyAppClassTask;
use NckRtl\Launch\Setup\App\CopyAppControllersTask;
use NckRtl\Launch\Setup\App\CopyAppMiddlewareTask;
use NckRtl\Launch\Setup\App\CopyAppRequestsTask;
use NckRtl\Launch\Setup\App\CopyAppTestsTask;
use NckRtl\Launch\Setup\App\CopyFrontendBootstrapTask;
use NckRtl\Launch\Setup\App\InstallAppReactScaffoldTask;
use NckRtl\Launch\Setup\App\RunSetupAuthTask;
use NckRtl\Launch\Setup\Tasks\GenerateRoutesTask;

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
