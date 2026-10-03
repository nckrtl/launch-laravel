<?php

declare(strict_types=1);

namespace {{namespace}}Http\Controllers\Settings;

use {{namespace}}Http\Controllers\Controller;
use {{namespace}}Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
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
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()?->fill($request->validated());

        if ($request->user()?->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()?->save();

        return to_route('settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
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
