<?php
namespace Nexus\Modules\Http;

/**
 * API Resource wrapper for consistent JSON responses
 */
class ApiResource {
    /**
     * Success response
     */
    public static function success($data = null, string $message = '', int $statusCode = 200, array $headers = []) {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return Response::json($response, $statusCode, $headers);
    }

    /**
     * Error response
     */
    public static function error(string $message, int $statusCode = 400, array $errors = [], array $headers = []) {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return Response::json($response, $statusCode, $headers);
    }

    /**
     * Collection response with pagination
     */
    public static function collection($items, string $message = '', array $meta = []) {
        $response = [
            'success' => true,
            'data' => $items,
            'count' => is_array($items) ? count($items) : $items->count(),
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return Response::json($response);
    }

    /**
     * Single resource response
     */
    public static function resource($resource, string $message = '') {
        $response = [
            'success' => true,
            'data' => $resource,
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return Response::json($response);
    }

    /**
     * Validation error response
     */
    public static function validationError(array $errors, string $message = 'Validation failed') {
        return self::error($message, 422, $errors);
    }

    /**
     * Not found response
     */
    public static function notFound(string $resource = 'Resource') {
        return self::error($resource . ' not found', 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized') {
        return self::error($message, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden') {
        return self::error($message, 403);
    }
}