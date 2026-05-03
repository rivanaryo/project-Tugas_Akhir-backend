<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Request\LoginAdminRequest;
use App\Modules\Admin\Services\JwtTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    public function __construct(private readonly JwtTokenService $jwtTokenService)
    {
    }

    public function login(LoginAdminRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $admin = $this->jwtTokenService->findUserByEmail($credentials['email']);

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password tidak valid.',
            ], 401);
        }

        $token = $this->jwtTokenService->issueToken($admin);

        $response = response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'expires_in' => $this->jwtTokenService->getTtlInSeconds(),
                'user' => $admin,
            ],
        ], 200);

        return $response->cookie($this->makeAccessTokenCookie($token));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data admin berhasil diambil.',
            'data' => $request->user(),
        ], 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $admin = $request->user();
        $payload = $request->attributes->get('jwt_payload');

        if (is_array($payload)) {
            $this->jwtTokenService->revokeTokenPayload($payload);
        }

        $token = $this->jwtTokenService->issueToken($admin);

        $response = response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui.',
            'data' => [
                'expires_in' => $this->jwtTokenService->getTtlInSeconds(),
            ],
        ], 200);

        return $response->cookie($this->makeAccessTokenCookie($token));
    }

    public function logout(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload');

        if (is_array($payload)) {
            $this->jwtTokenService->revokeTokenPayload($payload);
        }

        $response = response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);

        return $response->withoutCookie(
            (string) config('jwt.cookie_name'),
            (string) config('jwt.cookie_path', '/'),
            config('jwt.cookie_domain') ?: null,
            (bool) config('jwt.cookie_secure', false),
            false,
            (string) config('jwt.cookie_same_site', 'lax')
        );
    }

    private function makeAccessTokenCookie(string $token): Cookie
    {
        return Cookie::create(
            (string) config('jwt.cookie_name'),
            $token,
            0,
            (string) config('jwt.cookie_path', '/'),
            config('jwt.cookie_domain') ?: null,
            (bool) config('jwt.cookie_secure', false),
            (bool) config('jwt.cookie_http_only', true),
            false,
            (string) config('jwt.cookie_same_site', 'lax')
        );
    }
}