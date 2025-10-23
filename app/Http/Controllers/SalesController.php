<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Traits\ApiResponse;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;

class SalesController extends Controller
{
    use ApiResponse;

    public function __construct(private SalesService $salesService) {}

    public function store(CreateSaleRequest $request): SaleResource|JsonResponse
    {
        $sale = $this->salesService->createSale($request->items);

        return $this->success(
            new SaleResource($sale->load('items.product')),
            'Venda criada com sucesso',
            201
        );
    }

    public function show(int $id): SaleResource|JsonResponse
    {
        $sale = $this->salesService->getSaleDetails($id);

        if (! $sale) {
            return $this->notFound('Venda não encontrada');
        }

        return new SaleResource($sale->load('items.product'));
    }
}
