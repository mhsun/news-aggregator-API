<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function respondWithSuccess(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function respondError(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
