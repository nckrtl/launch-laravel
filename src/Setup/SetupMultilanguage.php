<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup;

use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\MultiLanguage\ConfigureI18nTask;
use NckRtl\Launch\Setup\MultiLanguage\CopyExamplePageTask;
use NckRtl\Launch\Setup\MultiLanguage\CopyLangDirectoryTask;

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
