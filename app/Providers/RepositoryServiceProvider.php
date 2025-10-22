<?php

namespace App\Providers;

use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleItemRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\InventoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleItemRepository;
use App\Repositories\SaleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(SaleItemRepositoryInterface::class, SaleItemRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
