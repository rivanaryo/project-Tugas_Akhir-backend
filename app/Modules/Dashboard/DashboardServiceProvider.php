<?php

namespace App\Modules\Dashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/dashboard')
            ->as('dashboard.')
            ->group(__DIR__.'/Routes/api.php');
    }
}