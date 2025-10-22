<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use App\Services\InventoryService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanOldInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(InventoryService $inventoryService): void
    {
        $deleted = $inventoryService->cleanOldInventory();

        // Opcional: logar a execução
        Log::info("CleanOldInventoryJob executado. Registros removidos: {$deleted}");
    }
}
