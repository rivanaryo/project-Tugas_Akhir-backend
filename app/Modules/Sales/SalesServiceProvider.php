<?php

namespace App\Modules\Sales;

use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use App\Modules\Sales\SalesOrder\Repositories\SalesOrderRepositories;
use App\Modules\Sales\SalesOrder\Services\SalesOrderTotalCalculator;
use App\Modules\Sales\SalesQuotation\Models\SalesQuotation;
use App\Modules\Sales\SalesQuotation\Reference\Models\SalesQuotationItems;
use App\Modules\Sales\SalesQuotation\Repositories\SalesQuotationItemsRepositories;
use App\Modules\Sales\SalesQuotation\Repositories\SalesQuotationRepositories;
use App\Modules\Sales\SalesQuotation\Services\SalesQuotationConverter;
use App\Modules\Sales\SalesQuotation\Services\SalesQuotationGrandTotalCalculator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SalesOrderRepositories::class, fn (): SalesOrderRepositories => new SalesOrderRepositories(new SalesOrder()));
        $this->app->singleton(SalesOrderTotalCalculator::class, fn (): SalesOrderTotalCalculator => new SalesOrderTotalCalculator());
        $this->app->bind(SalesQuotationRepositories::class, fn (): SalesQuotationRepositories => new SalesQuotationRepositories(new SalesQuotation()));
        $this->app->bind(SalesQuotationItemsRepositories::class, fn (): SalesQuotationItemsRepositories => new SalesQuotationItemsRepositories(new SalesQuotationItems()));
        $this->app->singleton(SalesQuotationGrandTotalCalculator::class, fn (): SalesQuotationGrandTotalCalculator => new SalesQuotationGrandTotalCalculator());
        $this->app->singleton(SalesQuotationConverter::class, fn (): SalesQuotationConverter => new SalesQuotationConverter());
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/sales')
            ->as('sales.')
            ->group(__DIR__.'/Routes/api.php');
    }
}
