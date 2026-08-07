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
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Passkeys;

class SecurityController extends Controller
{
    public static string $routePrefix = 'settings';

    #[Get(uri: 'security', middleware: ['auth', 'verified'])]
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $twoFactorPending = filled($user?->two_factor_secret)
            && ! ($user?->hasEnabledTwoFactorAuthentication() ?? false);

        return Inertia::render('settings/security', [
            'passwordRules' => 'minlength:8',
            'canManagePasskeys' => class_exists(Passkeys::class),
            'passkeys' => $user?->passkeys()
                ->latest()
                ->get()
                ->map(fn ($passkey): array => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'authenticator' => $passkey->authenticator,
                    'created_at_diff' => $passkey->created_at?->diffForHumans(),
                    'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                ])
                ->all() ?? [],
            'canManageTwoFactor' => Features::enabled(Features::twoFactorAuthentication()),
            'requiresConfirmation' => Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm',
            ),
            'twoFactorEnabled' => $user?->hasEnabledTwoFactorAuthentication() ?? false,
            'twoFactorPending' => $twoFactorPending,
            'continueTwoFactorSetup' => $twoFactorPending
                && $request->boolean('continueTwoFactorSetup'),
        ]);
    }

    #[Put(uri: 'security', middleware: 'auth')]
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
