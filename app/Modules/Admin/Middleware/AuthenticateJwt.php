<?php

namespace App\Modules\Admin\Middleware;

use App\Modules\Admin\Services\JwtTokenService;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class AuthenticateJwt
{
    public function __construct(private readonly JwtTokenService $jwtTokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $token = $request->cookie((string) config('jwt.cookie_name')) ?: $request->bearerToken();

        if (! $token) {
            return $this->unauthorizedResponse('Token tidak ditemukan.');
        }

        try {
            $payload = $this->jwtTokenService->decodeToken($token);

            if ($this->jwtTokenService->isTokenRevoked($payload)) {
                return $this->unauthorizedResponse('Token sudah dicabut.');
            }

            $admin = $this->jwtTokenService->findUserById($payload['sub'] ?? null);

            if (! $admin) {
                return $this->unauthorizedResponse('User token tidak valid.');
            }

            $request->attributes->set('jwt_payload', $payload);
            $request->setUserResolver(static fn () => $admin);
        } catch (ExpiredException) {
            return $this->unauthorizedResponse('Token sudah kedaluwarsa.');
        } catch (SignatureInvalidException|UnexpectedValueException) {
            return $this->unauthorizedResponse('Token tidak valid.');
        }

        return $next($request);
    }

    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}