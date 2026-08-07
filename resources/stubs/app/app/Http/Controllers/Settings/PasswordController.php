<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use HardImpact\Waymaker\Get;
use HardImpact\Waymaker\Put;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public static string $routePrefix = 'settings';

    /**
     * Show the user's password settings page.
     */
    #[Get(uri: 'password', name: 'settings.password.edit', middleware: 'auth')]
    public function edit(): RedirectResponse
    {
        return to_route('Settings.SecurityController.edit');
    }

    /**
     * Update the user's password.
     */
    #[Put(uri: 'password', name: 'settings.password.update', middleware: 'auth')]
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()?->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
