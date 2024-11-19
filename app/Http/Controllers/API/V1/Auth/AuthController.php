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
    /**
     * User registration
     *
     * @group Authentication
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password
     * @bodyParam password_confirmation required The password confirmation of the user. Example: password
     *
     * @response 201 {
     *   "message": "User registered successfully",
     *  "data": {
     *   "access_token": "xxx",
     *   "token_type": "Bearer"
     *  }
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *     "name": [
     *        "The name field is required."
     *     ],
     *    "email": [
     *      "The email field is required."
     *   ],
     *  "password": [
     *    "The password field is required."
     *  ]
     * }
     * }
     *
     * @param RegistrationRequest $request
     * @return JsonResponse
     *
     * */
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

    /**
     * User login
     *
     * @group Authentication
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password
     *
     * @response 200 {
     *  "message": "User logged in successfully",
     *  "data": {
     *      "access_token": "xxx",
     *     "token_type": "Bearer"
     *  }
     * }
     *
     * @response 401 {
     * "message": "Invalid credentials"
     * }
     *
     * @param Request $request
     * @return JsonResponse
     * */
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

    /**
     * User logout
     *
     * @group Authentication
     *
     * @response 200 {
     *  "message": "Successfully logged out"
     * }
     *
     * @param Request $request
     * @return JsonResponse
     * */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respondWithSuccess(
            message: 'Successfully logged out',
        );
    }
}
