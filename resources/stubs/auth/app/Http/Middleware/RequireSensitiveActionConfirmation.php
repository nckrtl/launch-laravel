<?php

declare(strict_types=1);

namespace {{namespace}}Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Wnx\TfaConfirmation\Http\Middleware\RequireTwoFactorAuthenticationConfirmation;

class RequireSensitiveActionConfirmation
{
    public function __construct(
        private readonly RequireTwoFactorAuthenticationConfirmation $requireTwoFactorConfirmation,
        private readonly RequirePassword $requirePassword,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($this->userHasEnabledTwoFactorAuthentication($user)) {
            return $this->requireTwoFactorConfirmation->handle($request, $next);
        }

        return $this->requirePassword->handle($request, $next);
    }

    private function userHasEnabledTwoFactorAuthentication(mixed $user): bool
    {
        if (! $user || ! method_exists($user, 'hasEnabledTwoFactorAuthentication')) {
            return false;
        }

        if (method_exists($user, 'getAttributes') && ! array_key_exists('two_factor_secret', $user->getAttributes())) {
            return false;
        }

        return $user->hasEnabledTwoFactorAuthentication();
    }
}
