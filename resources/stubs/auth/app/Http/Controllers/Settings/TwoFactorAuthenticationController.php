<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use {{namespace}}Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(TwoFactorAuthenticationRequest $request): RedirectResponse
    {
        $request->ensureStateIsValid();

        return to_route('settings.security.edit', [
            'continueTwoFactorSetup' => true,
        ]);
    }
}
