<?php

namespace App\Http\Controllers\Api;

use App\Domain\Contracts\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'scope'    => 'nullable|string',
        ]);

        $tokens = $this->auth->loginWithPassword(
            $data['email'],
            $data['password'],
            $data['scope'] ?? ''
        );

        return response()->json($tokens);
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => 'required|string',
            'scope'         => 'nullable|string',
        ]);

        $tokens = $this->auth->refreshToken(
            $data['refresh_token'],
            $data['scope'] ?? ''
        );

        return response()->json($tokens);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(Auth::user());
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logoutUser($request->user());
        return response()->json(['message' => 'logged_out']);
    }
}
