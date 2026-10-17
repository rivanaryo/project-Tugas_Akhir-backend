<?php

namespace App\Modules\Admin;

use App\Modules\Admin\Models\Admin;
use App\Modules\Admin\Repositories\AdminRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminRepositories::class, fn (): AdminRepositories => new AdminRepositories(new Admin()));
    }

    public function boot(): void
    {
        Route::middleware(['api'])
            ->prefix('api/admin')
            ->as('admin.')
            ->group(__DIR__.'/routes/api.php');

    }
}