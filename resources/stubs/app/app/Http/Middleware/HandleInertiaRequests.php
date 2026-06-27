<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $data = [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'location' => [
                'current' => $request->url(),
                'previous' => $request->headers->get('referer'),
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'navigation' => [
                'app' => [
                    'default' => '/dashboard',
                    'logout' => '/logout',
                    'settings' => '/settings/profile',
                    'main' => [
                        'items' => [
                            [
                                'title' => 'Dashboard',
                                'href' => '/dashboard',
                                'icon' => 'lucide:layout-grid',
                            ],
                        ],
                    ],
                    'footer' => [
                        'items' => [
                            [
                                'title' => 'GitHub Repo',
                                'href' => 'https://github.com/laravel/vue-starter-kit',
                                'icon' => 'GitHub',
                            ],
                        ],
                    ],
                    'user' => [
                        'items' => [
                            [
                                'title' => 'Settings',
                                'href' => '/settings/profile',
                                'icon' => 'Cog',
                            ],
                            [
                                'title' => 'Logout',
                                'href' => '/logout',
                                'method' => 'post',
                                'icon' => 'Logout',
                            ],
                        ],
                    ],
                ],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];

        return $data;
    }
}
