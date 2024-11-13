<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function respondWithSuccess(string $message, $data = [], int $status = 200): JsonResponse
    {
        $response['message'] = $message;

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    public function respondError(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
