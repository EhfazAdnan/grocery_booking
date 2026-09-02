<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $user = $this->authService->register($request->all());
        $token = $this->authService->issueToken($user);

        $response = $this->authService->tokenResponse($token);
        $response['message'] = 'User registered successfully';
        $response['user'] = $user;

        return response()->json($response, 201);
    }

    /**
     * Login user and return JWT token.
     */
    public function login(Request $request): JsonResponse
    {
        $token = $this->authService->login($request->all());

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $response = $this->authService->tokenResponse($token);
        $response['user'] = $this->authService->me();

        return response()->json($response, 200);
    }

    /**
     * Get authenticated user details.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(['user' => $user], 200);
    }

    /**
     * Logout user (invalidate token).
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        $token = $this->authService->refresh();

        return response()->json($this->authService->tokenResponse($token), 200);
    }
}
