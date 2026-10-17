<?php

namespace App\Modules\Rd;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'role.admin'])
            ->prefix('api/rd')
            ->as('rd.')
            ->group(__DIR__.'/routes/api.php');
    }
}
