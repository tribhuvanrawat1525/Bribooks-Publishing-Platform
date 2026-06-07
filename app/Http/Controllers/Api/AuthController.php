<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService
            ->register($request->validated());

        return response()->json($response);
    }

    public function login(LoginRequest $request)
    {
        $response = $this->authService
            ->login($request->validated());

        return response()->json($response);
    }

    public function profile()
    {
        $response = $this->authService->profile();

        return response()->json($response);
    }

    public function logout()
    {
        $response = $this->authService->logout();

        return response()->json($response);
    }
}