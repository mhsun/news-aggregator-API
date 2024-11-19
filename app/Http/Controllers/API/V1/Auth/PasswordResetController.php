<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send reset link via email
     *
     * @group Authentication
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     *
     * @response 200 {
     *  "message": "We have emailed your password reset link!"
     * }
     *
     * @response 422 {
     * "message": "We can't find a user with that email address."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     * */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->respondWithSuccess(message: __($status))
            : $this->respondError(message: __($status));
    }

    /**
     * Reset password
     *
     * @group Authentication
     *
     * @bodyParam token string required The token sent to the user's email. Example: xxx
     * @bodyParam email string required The email of the user. Example:
     * @bodyParam password string required The new password of the user. Example: password
     * @bodyParam password_confirmation required The new password confirmation of the user. Example: password
     *
     * @response 200 {
     * "message": "Password reset successfully"
     * }
     *
     * @response 422 {
     * "message": "This password reset token is invalid."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     * */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->respondWithSuccess(message: __($status))
            : $this->respondError(message: __($status));
    }
}
