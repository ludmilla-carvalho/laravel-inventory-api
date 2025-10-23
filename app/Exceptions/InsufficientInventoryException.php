<?php

namespace App\Exceptions;

use Exception;

class InsufficientInventoryException extends Exception
{
    public function __construct(int $productId)
    {
        parent::__construct("Estoque insuficiente para o produto {$productId}");
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 400);
    }
}
