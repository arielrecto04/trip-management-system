<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles){
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Ensure the user model exposes a roles relationship before calling it
        if (!method_exists($user, 'roles') || !$user->roles()->whereIn('slug', (array) $roles)->exists()) {
            // Unauthorized
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
