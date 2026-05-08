<?php

namespace App\Modules\Providers;

use App\Modules\Admin\AdminServiceProvider;
use Illuminate\Support\ServiceProvider;
use App\Modules\MasterData\MasterDataServiceProvider;
use App\Modules\Purchase\PurchaseServiceProvider;
use App\Modules\Sales\SalesServiceProvider;

class Providers extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(MasterDataServiceProvider::class);
        $this->app->register(PurchaseServiceProvider::class);
        $this->app->register(SalesServiceProvider::class);

        // Daftarkan provider modul lain di sini:
        // $this->app->register(BankingServiceProvider::class);
        // $this->app->register(InventoryServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
