<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\App;

use HardImpact\Craft\Setup\Tasks\InstallCraftReactRegistryItemsTask;

class InstallAppReactScaffoldTask extends InstallCraftReactRegistryItemsTask
{
    protected function items(): array
    {
        return ['@craft/craft-app-scaffold'];
    }

    public function description(): string
    {
        return 'Installing React app scaffold from Craft UI';
    }
}
