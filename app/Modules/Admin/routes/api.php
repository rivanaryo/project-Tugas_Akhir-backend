<?php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role.admin'])->group(function (): void {
	Route::get('me', [AuthController::class, 'me']);
	Route::post('logout', [AuthController::class, 'logout']);

	Route::get('users', [AdminController::class, 'index']);
	Route::post('users', [AdminController::class, 'store']);
	Route::get('users/{id}', [AdminController::class, 'show']);
	Route::match(['put', 'patch'], 'users/{id}', [AdminController::class, 'update']);
	Route::delete('users/{id}', [AdminController::class, 'destroy']);
});
