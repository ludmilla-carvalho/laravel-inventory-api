<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanOldInventoryJob;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CleanOldInventoryJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_calls_clean_old_inventory_service(): void
    {
        // Mock the InventoryService
        $inventoryService = $this->createMock(InventoryService::class);

        // Expect cleanOldInventory to be called once and return 5
        $inventoryService->expects($this->once())
            ->method('cleanOldInventory')
            ->willReturn(5);

        // Create and handle the job
        $job = new CleanOldInventoryJob;
        $job->handle($inventoryService);
    }

    public function test_job_logs_deleted_records_count(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('CleanOldInventoryJob executado. Registros removidos: 10');

        // Mock the InventoryService
        $inventoryService = $this->createMock(InventoryService::class);
        $inventoryService->method('cleanOldInventory')
            ->willReturn(10);

        // Create and handle the job
        $job = new CleanOldInventoryJob;
        $job->handle($inventoryService);
    }

    public function test_job_logs_zero_when_no_records_deleted(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('CleanOldInventoryJob executado. Registros removidos: 0');

        // Mock the InventoryService
        $inventoryService = $this->createMock(InventoryService::class);
        $inventoryService->method('cleanOldInventory')
            ->willReturn(0);

        // Create and handle the job
        $job = new CleanOldInventoryJob;
        $job->handle($inventoryService);
    }

    public function test_job_implements_should_queue(): void
    {
        $job = new CleanOldInventoryJob;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    public function test_job_can_be_dispatched(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        CleanOldInventoryJob::dispatch();

        \Illuminate\Support\Facades\Queue::assertPushed(CleanOldInventoryJob::class);
    }
}
