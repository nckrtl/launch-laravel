<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use {{namespace}}Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user->refresh())
            ->get('/settings/security');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/security')
                ->where('twoFactorEnabled', false)
                ->where('twoFactorPending', false)
                ->where('canManagePasskeys', true)
                ->where('passkeys', [])
                ->has('canManageTwoFactor')
                ->has('requiresConfirmation')
                ->has('passwordRules'));
    }

    public function test_users_can_manage_passkeys(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(PasskeyUser::class, $user);
        $this->assertFalse($user->hasPasskeysEnabled());
        $this->assertCount(0, $user->passkeys);
    }
}
