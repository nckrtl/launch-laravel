<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers;

class DashboardController extends Controller
{
    public function show(): \Inertia\ResponseFactory|\Inertia\Response
    {
        return inertia('Dashboard');
    }
}
