<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup;

use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Auth\CleanupLegacyAuthVueFilesTask;
use NckRtl\Launch\Setup\Auth\ConfigureAuthFrontendBootstrapTask;
use NckRtl\Launch\Setup\Auth\ConfigurePasskeysTask;
use NckRtl\Launch\Setup\Auth\CopyAuthTestsTask;
use NckRtl\Launch\Setup\Auth\CopyFortifyFilesTask;
use NckRtl\Launch\Setup\Auth\CopyLoginLinkConfigTask;
use NckRtl\Launch\Setup\Auth\InstallAuthComposerPackagesTask;
use NckRtl\Launch\Setup\Auth\InstallAuthReactScaffoldTask;
use NckRtl\Launch\Setup\Auth\InstallLoginLinkTask;
use NckRtl\Launch\Setup\Auth\PublishMigrationsTask;
use NckRtl\Launch\Setup\Auth\RegisterFortifyServiceProviderTask;
use NckRtl\Launch\Setup\Auth\RegisterPasskeyRoutesTask;
use NckRtl\Launch\Setup\Auth\UpdateDatabaseSeederTask;
use NckRtl\Launch\Setup\Auth\UpdateUserModelTask;
use NckRtl\Launch\Setup\Auth\UpdateUsersMigrationTask;

class SetupAuth extends Setup
{
    /**
     * The tasks to run.
     *
     * @var array
     */
    protected $tasks = [
        InstallAuthComposerPackagesTask::class,
        InstallLoginLinkTask::class,
        CopyFortifyFilesTask::class,
        CopyLoginLinkConfigTask::class,
        RegisterFortifyServiceProviderTask::class,
        RegisterPasskeyRoutesTask::class,
        ConfigurePasskeysTask::class,
        UpdateUserModelTask::class,
        UpdateUsersMigrationTask::class,
        InstallAuthReactScaffoldTask::class,
        ConfigureAuthFrontendBootstrapTask::class,
        CleanupLegacyAuthVueFilesTask::class,
        CopyAuthTestsTask::class,
        PublishMigrationsTask::class,
        UpdateDatabaseSeederTask::class,
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
