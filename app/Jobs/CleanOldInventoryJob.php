<?php

namespace App\Jobs;

use App\Services\InventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanOldInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(InventoryService $inventoryService): void
    {
        $deleted = $inventoryService->cleanOldInventory();

        Log::info("CleanOldInventoryJob executado. Registros removidos: {$deleted}");
    }
}
