<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\InstallCraftReactRegistryItemsTask;

class InstallAuthReactScaffoldTask extends InstallCraftReactRegistryItemsTask
{
    protected function items(): array
    {
        return ['@craft/craft-auth-scaffold'];
    }

    public function description(): string
    {
        return 'Installing React authentication scaffold from Craft UI';
    }
}
