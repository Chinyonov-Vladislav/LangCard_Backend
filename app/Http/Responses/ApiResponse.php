<?php

namespace App\Http\Responses;

use App\Enums\TypeStatus;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(string $message = null, object $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => TypeStatus::success->value,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message, object $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => TypeStatus::error->value,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
