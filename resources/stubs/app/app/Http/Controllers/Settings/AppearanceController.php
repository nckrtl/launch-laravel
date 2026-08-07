<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use HardImpact\Waymaker\Get;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    public static string $routePrefix = 'settings';

    #[Get(uri: 'appearance', name: 'appearance', middleware: 'auth')]
    public function edit(): Response
    {
        return Inertia::render('settings/appearance');
    }
}
