<?php

namespace App\Modules\Providers;

use App\Modules\Admin\AdminServiceProvider;
use Illuminate\Support\ServiceProvider;
use App\Modules\MasterData\MasterDataServiceProvider;
use App\Modules\Finance\FinanceServiceProvider;
use App\Modules\Sales\SalesServiceProvider;
use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Inventory\InventoryServiceProvider;

class Providers extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(MasterDataServiceProvider::class);
        $this->app->register(FinanceServiceProvider::class);
        $this->app->register(SalesServiceProvider::class);
        $this->app->register(DashboardServiceProvider::class);
        $this->app->register(InventoryServiceProvider::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
