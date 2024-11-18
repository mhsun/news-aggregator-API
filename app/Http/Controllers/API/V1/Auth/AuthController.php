<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegistrationRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken(config('app.name'))->plainTextToken;

        return $this->respondWithSuccess(
            message: 'User registered successfully',
            data: [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            status: 201
        );
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! auth()->attempt($credentials)) {
            return $this->respondError(
                message: 'Invalid credentials',
                status: 401
            );
        }

        /** @var User $user */
        $user = auth()->user();
        $token = $user->createToken(config('app.name'))->plainTextToken;

        return $this->respondWithSuccess(
            message: 'User logged in successfully',
            data: [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respondWithSuccess(
            message: 'Successfully logged out',
        );
    }
}
