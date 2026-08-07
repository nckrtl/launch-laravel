<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup;

use HardImpact\Launch\Setup\MultiLanguage\ConfigureI18nTask;
use HardImpact\Launch\Setup\MultiLanguage\CopyExamplePageTask;
use HardImpact\Launch\Setup\MultiLanguage\CopyLangDirectoryTask;
use HardImpact\Launch\Setup\Tasks\GenerateRoutesTask;
use Illuminate\Filesystem\Filesystem;

class SetupMultilanguage extends Setup
{
    /**
     * The tasks to run.
     *
     * Note: Vite i18n configuration is already included in the starterkit.
     * This setup only copies language files and example components.
     *
     * @var array
     */
    protected $tasks = [
        CopyLangDirectoryTask::class,
        CopyExamplePageTask::class,
        ConfigureI18nTask::class,
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
