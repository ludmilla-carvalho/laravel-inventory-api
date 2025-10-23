<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Http\Traits\ApiResponse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    use ApiResponse;

    public function __construct(private InventoryService $inventoryService) {}

    public function index(): JsonResponse
    {
        $summary = $this->inventoryService->getInventorySummary();

        return $this->success(
            InventoryResource::collection($summary),
            'Resumo do estoque'
        );
    }

    public function store(AddInventoryRequest $request)
    {
        $this->inventoryService->addStock(
            $request->sku,
            $request->quantity,
            $request->cost_price
        );

        return $this->success(null, 'Estoque atualizado com sucesso', Response::HTTP_CREATED);
    }

    // TODO: verificar
    // public function clean(): JsonResponse
    // {
    //     $deleted = $this->inventoryService->cleanOldInventory();

    //     return $this->success(['deleted' => $deleted], 'Itens antigos removidos');
    // }
}
