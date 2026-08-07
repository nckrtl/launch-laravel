<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup;

use HardImpact\Launch\Setup\Auth\CleanupLegacyAuthVueFilesTask;
use HardImpact\Launch\Setup\Auth\ConfigureAuthFrontendBootstrapTask;
use HardImpact\Launch\Setup\Auth\ConfigurePasskeysTask;
use HardImpact\Launch\Setup\Auth\CopyAuthTestsTask;
use HardImpact\Launch\Setup\Auth\CopyFortifyFilesTask;
use HardImpact\Launch\Setup\Auth\CopyLoginLinkConfigTask;
use HardImpact\Launch\Setup\Auth\InstallAuthComposerPackagesTask;
use HardImpact\Launch\Setup\Auth\InstallAuthReactScaffoldTask;
use HardImpact\Launch\Setup\Auth\InstallLoginLinkTask;
use HardImpact\Launch\Setup\Auth\PublishMigrationsTask;
use HardImpact\Launch\Setup\Auth\RegisterFortifyServiceProviderTask;
use HardImpact\Launch\Setup\Auth\RegisterPasskeyRoutesTask;
use HardImpact\Launch\Setup\Auth\UpdateDatabaseSeederTask;
use HardImpact\Launch\Setup\Auth\UpdateUserModelTask;
use HardImpact\Launch\Setup\Auth\UpdateUsersMigrationTask;
use Illuminate\Filesystem\Filesystem;

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
