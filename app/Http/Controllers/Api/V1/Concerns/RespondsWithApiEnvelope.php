<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Http\JsonResponse;

trait RespondsWithApiEnvelope
{
    /**
     * @param  array<string, mixed>|object|null  $data
     */
    protected function successResponse(
        string $message,
        array|object|null $data = null,
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function errorResponse(
        string $message,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
