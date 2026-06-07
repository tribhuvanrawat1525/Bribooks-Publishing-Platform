<?php

namespace App\Helpers;
use App\Constants\ApiStatus;

class ApiResponse
{
    public static function success( string $message = 'Success', array $data = []): array {
        return [
            'code' => ApiStatus::SUCCESS,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function error( string $message = 'Error', int $code = 201): array {
        return [
            'code' => $code,
            'message' => $message
        ];
    }
}