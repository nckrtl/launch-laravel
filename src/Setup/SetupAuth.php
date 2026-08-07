<?php

namespace HardImpact\Craft\Setup;

use HardImpact\Craft\Setup\Auth\CleanupLegacyAuthVueFilesTask;
use HardImpact\Craft\Setup\Auth\ConfigureAuthFrontendBootstrapTask;
use HardImpact\Craft\Setup\Auth\ConfigurePasskeysTask;
use HardImpact\Craft\Setup\Auth\CopyAuthTestsTask;
use HardImpact\Craft\Setup\Auth\CopyFortifyFilesTask;
use HardImpact\Craft\Setup\Auth\CopyLoginLinkConfigTask;
use HardImpact\Craft\Setup\Auth\InstallAuthComposerPackagesTask;
use HardImpact\Craft\Setup\Auth\InstallAuthReactScaffoldTask;
use HardImpact\Craft\Setup\Auth\InstallFortifyTask;
use HardImpact\Craft\Setup\Auth\InstallLoginLinkTask;
use HardImpact\Craft\Setup\Auth\PublishMigrationsTask;
use HardImpact\Craft\Setup\Auth\RegisterFortifyServiceProviderTask;
use HardImpact\Craft\Setup\Auth\RegisterPasskeyRoutesTask;
use HardImpact\Craft\Setup\Auth\UpdateDatabaseSeederTask;
use HardImpact\Craft\Setup\Auth\UpdateUserModelTask;
use HardImpact\Craft\Setup\Auth\UpdateUsersMigrationTask;
use Illuminate\Filesystem\Filesystem;

class SetupAuth extends Setup
{
    /**
     * The tasks to run.
     *
     * @var array
     */
    protected $tasks = [
        InstallFortifyTask::class,
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
