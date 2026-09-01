<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    public function register(array $data): User
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => Role::CUSTOMER,
        ]);

        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }

    public function login(array $credentials): string|false
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var JWTGuard $guard */
        $guard = auth('api');

        $token = $guard->attempt($credentials);

        if ($token) {
            $user = $guard->user();

            Log::info('User logged in successfully', [
                'user_id' => $user?->id,
                'email' => $credentials['email'],
            ]);
        } else {
            Log::warning('Failed login attempt', [
                'email' => $credentials['email'],
            ]);
        }

        return $token;
    }

    public function me(): ?User
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard->user();
    }

    public function logout(): void
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $user = $guard->user();

        Log::info('User logged out', [
            'user_id' => $user?->id,
        ]);

        $guard->logout();
    }

    public function refresh(): string
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $user = $guard->user();

        Log::info('Token refreshed', [
            'user_id' => $user?->id,
        ]);

        return $guard->refresh();
    }

    public function tokenResponse(string $token): array
    {
        $ttlMinutes = (int) env('JWT_TTL', 60);

        return [
            'message' => 'Authentication successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttlMinutes * 60,
        ];
    }
}
