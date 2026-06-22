<?php

namespace App\Modules\MasterData;

use App\Modules\MasterData\DataProduk\Repositories\CategoryRepositories;
use App\Modules\MasterData\DataProduk\Repositories\ProductRepositories;
use App\Modules\MasterData\DataProduk\Repositories\UnitsRepository;
use App\Modules\MasterData\DataProduk\References\Models\Category;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementWarehouse\Repositories\WarehouseRepositories;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MasterDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositories::class, fn () => new ProductRepositories(new Product()));
        $this->app->bind(CategoryRepositories::class, fn () => new CategoryRepositories(new Category()));
        $this->app->bind(UnitsRepository::class, fn () => new UnitsRepository(new Units()));
        $this->app->bind(WarehouseRepositories::class, fn () => new WarehouseRepositories(new Warehouse()));
    }

    public function boot(): void
    {
        Route::middleware(['api', 'jwt.auth', 'role.admin'])
            ->prefix('api/master-data')
            ->as('master-data.')
            ->group(__DIR__.'/routes/api.php');
    }
}
