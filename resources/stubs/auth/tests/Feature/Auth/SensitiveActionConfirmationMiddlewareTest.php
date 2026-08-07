<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use {{namespace}}Http\Middleware\RequireSensitiveActionConfirmation;
use {{namespace}}Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\RecoveryCode;
use Tests\TestCase;

class SensitiveActionConfirmationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_two_factor_authentication_fall_back_to_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->registerSensitiveRoute();

        $response = $this
            ->actingAs($user)
            ->getJson('/_test/sensitive-action');

        $response
            ->assertStatus(423)
            ->assertJson(['message' => 'Password confirmation required.']);
    }

    public function test_users_with_two_factor_authentication_must_confirm_an_otp_code(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode([RecoveryCode::generate()])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->registerSensitiveRoute();

        $response = $this
            ->actingAs($user)
            ->getJson('/_test/sensitive-action');

        $response
            ->assertStatus(423)
            ->assertJson(['message' => 'Two factor authentication required.']);
    }


    public function test_passkey_registration_options_fall_back_to_password_confirmation_without_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/user/passkeys/options');

        $response
            ->assertStatus(423)
            ->assertJson(['message' => 'Password confirmation required.']);
    }

    public function test_passkey_registration_options_require_otp_confirmation_with_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode([RecoveryCode::generate()])),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/user/passkeys/options');

        $response
            ->assertStatus(423)
            ->assertJson(['message' => 'Two factor authentication required.']);
    }

    public function test_users_with_recent_two_factor_confirmation_can_continue(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode([RecoveryCode::generate()])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->registerSensitiveRoute();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.two_factor_confirmed_at' => now()->unix()])
            ->getJson('/_test/sensitive-action');

        $response
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    private function registerSensitiveRoute(): void
    {
        Route::middleware(['web', 'auth', RequireSensitiveActionConfirmation::class])
            ->get('/_test/sensitive-action', fn () => response()->json(['ok' => true]));
    }
}
