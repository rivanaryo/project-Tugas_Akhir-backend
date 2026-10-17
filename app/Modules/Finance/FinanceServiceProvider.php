<?php

namespace App\Modules\Finance;

use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use App\Modules\Finance\VendorPayment\Repositories\VendorPaymentRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VendorPaymentRepositories::class, fn (): VendorPaymentRepositories => new VendorPaymentRepositories(new VendorPayment()));
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/finance')
            ->as('finance.')
            ->group(__DIR__.'/routes/api.php');
    }
}
