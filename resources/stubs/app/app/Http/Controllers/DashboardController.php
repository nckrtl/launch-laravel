<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers;

use HardImpact\Waymaker\Get;

class DashboardController extends Controller
{
    #[Get(uri: '', middleware: 'auth')]
    public function show(): \Inertia\ResponseFactory|\Inertia\Response
    {
        return inertia('Dashboard');
    }
}
