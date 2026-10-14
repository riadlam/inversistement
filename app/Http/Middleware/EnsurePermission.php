<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard: `->middleware('permission:reports.export')`.
 *
 * Accepts several keys, granting access when the user holds any of them:
 * `permission:cp.validate,cp.request`.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->is_active) {
            return $this->deny($request, 401);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission) && $this->tokenAllows($user, $permission)) {
                return $next($request);
            }
        }

        return $this->deny($request, 403);
    }

    /**
     * When the request is authenticated by a Sanctum token, the token's
     * abilities cap what the role allows. Session requests have no token and
     * are governed by the role alone.
     */
    private function tokenAllows(mixed $user, string $permission): bool
    {
        if (! method_exists($user, 'currentAccessToken')) {
            return true;
        }

        $token = $user->currentAccessToken();

        if ($token === null) {
            return true;
        }

        return $user->tokenCan($permission) || $user->tokenCan('*');
    }

    private function deny(Request $request, int $status): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $status === 401
                    ? 'Authentification requise.'
                    : 'Vous n\'avez pas les droits nécessaires pour cette action.',
            ], $status);
        }

        abort($status, $status === 401
            ? 'Authentification requise.'
            : 'Vous n\'avez pas les droits nécessaires pour cette action.');
    }
}
