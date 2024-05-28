<?php
namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();


        if ($token && $this->isValidToken($token)) {
            $user = JWTAuth::toUser($token);
            $request->merge(['jwtUserId' => $user->id]);            
            return $next($request);
        }

        return response()->json(['message' => 'Hết phiên đăng nhập'], 401);
    }

    protected function isValidToken($token)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
