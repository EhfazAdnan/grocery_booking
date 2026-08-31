<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTGuard;

class AuthController
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => Role::CUSTOMER,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Login user and return JWT token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        /** @var JWTGuard $guard */
        $guard = auth('api');
        if (!$token = $guard->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get authenticated user details.
     */
    public function me(): JsonResponse
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $user = $guard->user();

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
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $guard->logout();

        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->refresh();

        return $this->respondWithToken($token);
    }

    /**
     * Return JWT token response.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        // Use the env value directly because the package config is not always indexed by IDEs,
        // and config('jwt.ttl') can trigger false "Config [jwt.ttl] not found" warnings.
        $ttlMinutes = (int) env('JWT_TTL', 60);

        return response()->json([
            'message' => 'Authentication successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttlMinutes * 60,
        ], 200);
    }
}
