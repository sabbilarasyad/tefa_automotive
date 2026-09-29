<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Berhasil.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400, array $extra = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return response()->json(array_merge($response, $extra), $status);
    }
}