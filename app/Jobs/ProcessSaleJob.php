<?php

namespace App\Jobs;

use App\Enums\SaleStatus;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\SalesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessSaleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $saleId) {}

    public function handle(SalesService $salesService, SaleRepositoryInterface $saleRepository): void
    {
        try {
            $salesService->finalizeSale($this->saleId);
            Cache::forget('inventory:summary');
        } catch (\Throwable $e) {
            $saleRepository->update($this->saleId, [
                'status' => SaleStatus::Failed,
            ]);
            Log::error("Erro ao processar venda {$this->saleId}: {$e->getMessage()}");
            throw $e;
        }
    }
}
