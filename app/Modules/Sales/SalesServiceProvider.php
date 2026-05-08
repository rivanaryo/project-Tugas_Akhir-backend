<?php

namespace App\Modules\Sales;

use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use App\Modules\Sales\SalesOrder\Repositories\SalesOrderRepositories;
use App\Modules\Sales\SalesOrder\Services\SalesOrderTotalCalculator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SalesOrderRepositories::class, fn (): SalesOrderRepositories => new SalesOrderRepositories(new SalesOrder()));
        $this->app->singleton(SalesOrderTotalCalculator::class, fn (): SalesOrderTotalCalculator => new SalesOrderTotalCalculator());
    }

    public function boot(): void
    {
        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/sales')
            ->as('sales.')
            ->group(__DIR__.'/Routes/api.php');
    }
}
