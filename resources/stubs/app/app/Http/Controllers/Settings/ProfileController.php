<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use {{namespace}}Http\Requests\Settings\ProfileUpdateRequest;
use HardImpact\Waymaker\Delete;
use HardImpact\Waymaker\Get;
use HardImpact\Waymaker\Patch;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public static string $routePrefix = 'settings';

    /**
     * Show the user's profile settings page.
     */
    #[Get(uri: 'profile', middleware: 'auth')]
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'auth' => [
                'user' => $request->user(),
            ],
            'mustVerifyEmail' => $request->user() instanceof Authenticatable,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    #[Patch(uri: 'profile', middleware: 'auth')]
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()?->fill($request->validated());

        if ($request->user()?->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()?->save();

        return to_route('Settings.ProfileController.edit');
    }

    /**
     * Delete the user's profile.
     */
    #[Delete(uri: 'profile', middleware: 'auth')]
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user) {
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
