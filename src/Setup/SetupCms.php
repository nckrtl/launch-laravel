<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup;

use HardImpact\Launch\Setup\Cms\ConfigureFilamentAuthRedirectTask;
use HardImpact\Launch\Setup\Cms\CopyAppClassTask;
use HardImpact\Launch\Setup\Cms\CopyCmsFilesTask;
use HardImpact\Launch\Setup\Cms\InstallFilamentComposerPackageTask;
use HardImpact\Launch\Setup\Cms\InstallNpmPackagesTask;
use HardImpact\Launch\Setup\Cms\RegisterFilamentServiceProviderTask;
use HardImpact\Launch\Setup\Cms\RunFilamentPublishAssetsTask;
use HardImpact\Launch\Setup\Cms\RunSetupAuthTask;
use HardImpact\Launch\Setup\Tasks\GenerateRoutesTask;
use Illuminate\Filesystem\Filesystem;

class SetupCms extends Setup
{
    /**
     * The tasks to run.
     *
     * @var array
     */
    protected $tasks = [
        CopyAppClassTask::class,
        RunSetupAuthTask::class,
        ConfigureFilamentAuthRedirectTask::class,
        InstallFilamentComposerPackageTask::class,
        CopyCmsFilesTask::class,
        RegisterFilamentServiceProviderTask::class,
        RunFilamentPublishAssetsTask::class,
        InstallNpmPackagesTask::class,
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
