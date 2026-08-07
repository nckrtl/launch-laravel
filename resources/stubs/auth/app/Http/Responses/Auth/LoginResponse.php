<?php

declare(strict_types=1);

namespace {{namespace}}Http\Responses\Auth;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): SymfonyResponse
    {
        /** @var Request $request */
        $url = redirect()->intended(config('fortify.home'))->getTargetUrl();

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        if ($request->header('X-Inertia') && $this->isNonInertiaDestination($url)) {
            return Inertia::location($url);
        }

        return redirect()->to($url);
    }

    private function isNonInertiaDestination(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $path = trim($path, '/');

        foreach (config('fortify.non_inertia_paths', []) as $nonInertiaPath) {
            $nonInertiaPath = trim((string) $nonInertiaPath, '/');

            if ($nonInertiaPath === '') {
                continue;
            }

            if ($path === $nonInertiaPath || str_starts_with($path, "{$nonInertiaPath}/")) {
                return true;
            }
        }

        return false;
    }
}
