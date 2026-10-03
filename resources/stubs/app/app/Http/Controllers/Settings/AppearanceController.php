<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/appearance');
    }
}
