<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => Role::CUSTOMER,
        ]);
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

        return $guard->attempt($credentials);
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
        $guard->logout();
    }

    public function refresh(): string
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

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
