<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Traits\ApiResponse;
use App\Services\SalesService;
use Illuminate\Http\Response;

class SalesController extends Controller
{
    use ApiResponse;

    public function __construct(private SalesService $salesService) {}

    public function store(CreateSaleRequest $request)
    {
        $sale = $this->salesService->createSale($request->items);

        return $this->success(
            new SaleResource($sale->load('items.product')),
            'Venda registrada com sucesso',
            Response::HTTP_CREATED
        );
    }

    public function show(int $id)
    {
        $sale = $this->salesService->getSaleDetails($id);

        if (! $sale) {
            return $this->notFound('Venda não encontrada');
        }

        return $this->success(new SaleResource($sale->load('items.product')));
    }
}
