<?php

namespace App\Modules\Inventory;

use App\Modules\Inventory\StockBalance\Repositories\StockBalanceRepository;
use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\Inventory\StockOpname\Models\StockOpname;
use App\Modules\Inventory\StockOpname\Repositories\StockOpnameRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StockBalanceRepository::class, fn (): StockBalanceRepository => new StockBalanceRepository());
        $this->app->bind(StockMovementRepositories::class, fn (): StockMovementRepositories => new StockMovementRepositories(new StockMovement()));
        $this->app->bind(StockOpnameRepositories::class, fn (): StockOpnameRepositories => new StockOpnameRepositories(new StockOpname()));
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/inventory')
            ->as('inventory.')
            ->group(__DIR__.'/routes/api.php');

        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/inventory')
            ->as('inventory.')
            ->group(__DIR__.'/StockOpname/routes/api.php');

        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/inventory/rop')
            ->as('inventory.rop.')
            ->group(__DIR__.'/Prediction/routes/api.php');
    }
}
