<?php

namespace App\Services;

use App\Constants\Roles;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthService
{
    public function register(array $data): array
    {
        try {

            $exists = DB::table('users')
                ->where('email', $data['email'])
                ->exists();

            if ($exists) {
                return ApiResponse::error('Email already exists');
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'role'       => Roles::AUTHOR,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ApiResponse::success(
                'User registered successfully',
                [
                    'user_id' => $userId
                ]
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function login(array $data): array
    {
        try {

            $credentials = [
                'email' => $data['email'],
                'password' => $data['password']
            ];

            if (!$token = JWTAuth::attempt($credentials)) {

                return ApiResponse::error(
                    'Invalid credentials'
                );
            }

            $user = auth()->user();

            return ApiResponse::success(
                'Login successful',
                [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function profile(): array
    {
        $user = auth()->user();

        return ApiResponse::success(
            'Profile fetched successfully',
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        );
    }

    public function logout(): array
    {
        JWTAuth::invalidate(
            JWTAuth::getToken()
        );

        return ApiResponse::success(
            'Logout successful'
        );
    }
}