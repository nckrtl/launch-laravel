<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\App;

use HardImpact\Launch\Setup\Tasks\InstallLaunchReactRegistryItemsTask;

class InstallAppReactScaffoldTask extends InstallLaunchReactRegistryItemsTask
{
    protected function items(): array
    {
        return ['@launch/launch-app-scaffold'];
    }

    public function description(): string
    {
        return 'Installing React app scaffold from Launch UI';
    }
}
