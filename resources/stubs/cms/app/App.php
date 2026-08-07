<?php

declare(strict_types=1);

namespace {{namespace}};

class App
{
    public static function getRedirectRouteAfterLogin(): string
    {
        return 'filament.admin.pages.dashboard';
    }
}
