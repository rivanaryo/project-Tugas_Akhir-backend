<?php

namespace App\Modules\Inventory;

use App\Modules\Inventory\GoodReceipt\Models\GoodReceipt;
use App\Modules\Inventory\GoodReceipt\Observers\GoodReceiptItemsObserver;
use App\Modules\Inventory\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Inventory\GoodReceipt\Repositories\GoodReceiptItemsRepositories;
use App\Modules\Inventory\GoodReceipt\Repositories\GoodReceiptRepositories;
use App\Modules\Inventory\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Inventory\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Inventory\PurchaseOrder\Repositories\PurchaseOrderItemsRepositories;
use App\Modules\Inventory\PurchaseOrder\Repositories\PurchaseOrderRepositories;
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
        $this->app->bind(StockMovementRepositories::class, fn (): StockMovementRepositories => new StockMovementRepositories(new StockMovement()));
        $this->app->bind(StockOpnameRepositories::class, fn (): StockOpnameRepositories => new StockOpnameRepositories(new StockOpname()));
        $this->app->bind(PurchaseOrderRepositories::class, fn (): PurchaseOrderRepositories => new PurchaseOrderRepositories(new PurchaseOrder()));
        $this->app->bind(PurchaseOrderItemsRepositories::class, fn (): PurchaseOrderItemsRepositories => new PurchaseOrderItemsRepositories(new PurchaseOrderItems()));
        $this->app->bind(GoodReceiptRepositories::class, fn (): GoodReceiptRepositories => new GoodReceiptRepositories(new GoodReceipt()));
        $this->app->bind(GoodReceiptItemsRepositories::class, fn (): GoodReceiptItemsRepositories => new GoodReceiptItemsRepositories(new GoodReceiptItems()));
    }

    public function boot(): void
    {
        GoodReceiptItems::observe(GoodReceiptItemsObserver::class);

        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/inventory')
            ->as('inventory.')
            ->group(__DIR__.'/routes/api.php');

        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/inventory')
            ->as('inventory.')
            ->group(__DIR__.'/StockOpname/routes/api.php');

        Route::middleware(['api'])
            ->prefix('api/inventory/rop')
            ->as('inventory.rop.')
            ->group(__DIR__.'/Prediction/routes/api.php');
    }
}