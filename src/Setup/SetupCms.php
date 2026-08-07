<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup;

use NckRtl\Launch\Setup\Cms\ConfigureFilamentAuthRedirectTask;
use NckRtl\Launch\Setup\Cms\CopyAppClassTask;
use NckRtl\Launch\Setup\Cms\CopyCmsFilesTask;
use NckRtl\Launch\Setup\Cms\InstallFilamentComposerPackageTask;
use NckRtl\Launch\Setup\Cms\InstallNpmPackagesTask;
use NckRtl\Launch\Setup\Cms\RegisterFilamentServiceProviderTask;
use NckRtl\Launch\Setup\Cms\RunFilamentPublishAssetsTask;
use NckRtl\Launch\Setup\Cms\RunSetupAuthTask;
use NckRtl\Launch\Setup\Tasks\GenerateRoutesTask;
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
