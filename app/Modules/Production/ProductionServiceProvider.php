<?php

namespace App\Modules\Production;

use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\Production\GoodReceipt\Models\GoodReceipt;
use App\Modules\Production\GoodReceipt\Observers\GoodReceiptItemsObserver;
use App\Modules\Production\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Production\GoodReceipt\Repositories\GoodReceiptItemsRepositories;
use App\Modules\Production\GoodReceipt\Repositories\GoodReceiptRepositories;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Production\PurchaseOrder\Repositories\PurchaseOrderItemsRepositories;
use App\Modules\Production\PurchaseOrder\Repositories\PurchaseOrderRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PurchaseOrderRepositories::class, fn (): PurchaseOrderRepositories => new PurchaseOrderRepositories(new PurchaseOrder()));
        $this->app->bind(PurchaseOrderItemsRepositories::class, fn (): PurchaseOrderItemsRepositories => new PurchaseOrderItemsRepositories(new PurchaseOrderItems()));
        $this->app->bind(GoodReceiptRepositories::class, fn (): GoodReceiptRepositories => new GoodReceiptRepositories(new GoodReceipt(), app(StockMovementRepositories::class)));
        $this->app->bind(GoodReceiptItemsRepositories::class, fn (): GoodReceiptItemsRepositories => new GoodReceiptItemsRepositories(new GoodReceiptItems()));
    }

    public function boot(): void
    {
        GoodReceiptItems::observe(GoodReceiptItemsObserver::class);

        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/production')
            ->as('production.')
            ->group(__DIR__.'/routes/api.php');
    }
}
