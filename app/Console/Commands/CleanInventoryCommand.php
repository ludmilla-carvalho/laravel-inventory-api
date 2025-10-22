<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryService;

class CleanInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove registros antigos do estoque';

    /**
     * Execute the console command.
     */
    public function handle(InventoryService $inventoryService): int
    {
        $deleted = $inventoryService->cleanOldInventory();

        $this->info("Limpeza concluída. Registros removidos: {$deleted}");

        return Command::SUCCESS;
    }
}
