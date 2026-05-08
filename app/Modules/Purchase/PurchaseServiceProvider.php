<?php

namespace App\Modules\Purchase;

use App\Modules\Purchase\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Purchase\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Purchase\PurchaseOrder\Repositories\PurchaseOrderItemsRepositories;
use App\Modules\Purchase\PurchaseOrder\Repositories\PurchaseOrderRepositories;
use App\Modules\Purchase\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Purchase\GoodReceipt\Observers\GoodReceiptItemsObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PurchaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PurchaseOrderRepositories::class, fn (): PurchaseOrderRepositories => new PurchaseOrderRepositories(new PurchaseOrder()));
        $this->app->bind(PurchaseOrderItemsRepositories::class, fn (): PurchaseOrderItemsRepositories => new PurchaseOrderItemsRepositories(new PurchaseOrderItems()));
    }

    public function boot(): void
    {
        GoodReceiptItems::observe(GoodReceiptItemsObserver::class);

        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/purchase')
            ->as('purchase.')
            ->group(__DIR__.'/routes/api.php');
    }
}

