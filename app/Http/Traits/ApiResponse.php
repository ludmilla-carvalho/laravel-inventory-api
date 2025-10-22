<?php

namespace App\Http\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

trait ApiResponse
{
    protected function success(JsonResource|array|null $data = null, string $message = 'Operação realizada com sucesso', int $status = Response::HTTP_OK)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message = 'Ocorreu um erro', array $errors = [], int $status = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function notFound(string $message = 'Recurso não encontrado')
    {
        return $this->error($message, [], Response::HTTP_NOT_FOUND);
    }

    protected function validationError(array $errors, string $message = 'Erro de validação')
    {
        return $this->error($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
