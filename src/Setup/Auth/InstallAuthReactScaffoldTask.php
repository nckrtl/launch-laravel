<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Auth;

use HardImpact\Launch\Setup\Tasks\InstallLaunchReactRegistryItemsTask;

class InstallAuthReactScaffoldTask extends InstallLaunchReactRegistryItemsTask
{
    protected function items(): array
    {
        return ['@launch/launch-auth-scaffold'];
    }

    public function description(): string
    {
        return 'Installing React authentication scaffold from Launch UI';
    }
}
