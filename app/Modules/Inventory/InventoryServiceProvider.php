<?php

namespace App\Modules\Inventory;

use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StockMovementRepositories::class, fn (): StockMovementRepositories => new StockMovementRepositories(new StockMovement()));
    }

    public function boot(): void
    {
        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/inventory')
            ->as('inventory.')
            ->group(__DIR__.'/routes/api.php');
    }
}