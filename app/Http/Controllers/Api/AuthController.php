<?php

namespace App\Http\Controllers\Api;

use App\Actions\AuthAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthAction $authAction,
    ) {}

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'email'    => 'required|email|max:255',
        ]);

        $result = $this->authAction->check($request->username, $request->email);

        if (! $result['found']) {
            return response()->json([
                'exists' => false,
                'message' => 'Account not found. Contact your administrator.',
            ], 404);
        }

        if (! $result['is_active']) {
            return response()->json([
                'exists' => true,
                'has_password' => true,
                'message' => 'Your account is deactivated. Contact your administrator.',
            ], 403);
        }

        if ($result['has_password']) {
            return response()->json([
                'exists' => true,
                'has_password' => true,
                'message' => 'User exists. Please log in.',
            ]);
        }

        return response()->json([
            'exists' => true,
            'has_password' => false,
            'message' => 'Set your password to activate your account.',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $this->authAction->register(
                $request->username,
                $request->email,
                $request->password,
            );
        } catch (ValidationException $e) {
            $message = $e->errors()['username'][0] ?? $e->errors()['email'][0] ?? 'Registration failed.';

            if (str_contains($message, 'not found')) {
                return response()->json(['message' => $message], 404);
            }
            if (str_contains($message, 'deactivated')) {
                return response()->json(['message' => $message], 403);
            }
            if (str_contains($message, 'already has a password')) {
                return response()->json(['message' => $message], 409);
            }

            return response()->json(['message' => $message], 422);
        }

        return response()->json([
            'message' => 'Account activated successfully.',
            'user' => $this->authAction->identityPayload($user),
            'token' => $this->authAction->issueToken($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $user = $this->authAction->login($request->email, $request->password);
        } catch (ValidationException $e) {
            if (str_contains($e->errors()['email'][0] ?? '', 'deactivated')) {
                return response()->json([
                    'message' => 'Your account is deactivated. Contact your administrator.',
                ], 403);
            }
            throw $e;
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $this->authAction->identityPayload($user),
            'token' => $this->authAction->issueToken($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('wilaya');

        return response()->json([
            'user' => $this->authAction->identityPayload($user) + [
                'wilaya' => $user->wilaya,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
