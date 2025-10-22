<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou lógica de autorização
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|exists:products,sku',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
        ];
    }
}
