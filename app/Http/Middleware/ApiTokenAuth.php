<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        $token = null;

        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            $token = $request->query('api_token');
        }

        $user = User::findByApiToken($token);

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        Auth::login($user);
        return $next($request);
    }
}
