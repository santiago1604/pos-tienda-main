<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Respuesta exitosa estándar.
     */
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * Respuesta de creación exitosa (201).
     */
    protected function created(mixed $data = null, string $message = 'Recurso creado'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Respuesta de error estándar.
     */
    protected function error(string $message = 'Error', int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Error 401 – No autenticado.
     */
    protected function unauthorized(string $message = 'No autenticado'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Error 403 – Sin permisos.
     */
    protected function forbidden(string $message = 'No autorizado'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Error 404 – Recurso no encontrado.
     */
    protected function notFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Error 422 – Validación fallida.
     */
    protected function validationError(mixed $errors, string $message = 'Error de validación'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Respuesta paginada estándar.
     */
    protected function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
