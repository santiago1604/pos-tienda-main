<?php

/*
|--------------------------------------------------------------------------
| API Routes — StoreCell
|--------------------------------------------------------------------------
|
| Todas las rutas aquí quedan bajo el prefijo /api  y usan el middleware
| 'api' (stateful session). La autenticación se verifica con 'auth'.
|
| Estructura:
|   /api/auth/*        → autenticación (pública)
|   /api/products/*    → productos
|   /api/categories/*  → categorías
|   /api/sales/*       → ventas
|   /api/repairs/*     → reparaciones
|   /api/dashboard/*   → estadísticas [admin]
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\SaleApiController;
use App\Http\Controllers\Api\RepairApiController;
use App\Http\Controllers\Api\DashboardApiController;

// ─── Autenticación (pública) ────────────────────────────────────────────────
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('login',  [AuthApiController::class, 'login'])->name('login');
    Route::post('logout', [AuthApiController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
    Route::get('me',      [AuthApiController::class, 'me'])->name('me')->middleware('auth:sanctum');
});

// ─── Rutas protegidas ────────────────────────────────────────────────────────
// Usa el guard 'sanctum' para verificar el Bearer token en el header
// Authorization: Bearer <token>
Route::middleware(['auth:sanctum'])->group(function () {

    // Productos
    Route::prefix('products')->name('api.products.')->group(function () {
        Route::get('low-stock',    [ProductApiController::class, 'lowStock'])->name('low-stock');
        Route::get('/',            [ProductApiController::class, 'index'])->name('index');
        Route::get('{id}',         [ProductApiController::class, 'show'])->name('show');
        Route::post('/',           [ProductApiController::class, 'store'])->name('store')->middleware('role:admin');
        Route::put('{id}',         [ProductApiController::class, 'update'])->name('update')->middleware('role:admin');
        Route::delete('{id}',      [ProductApiController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    // Categorías
    Route::prefix('categories')->name('api.categories.')->group(function () {
        Route::get('/',       [CategoryApiController::class, 'index'])->name('index');
        Route::post('/',      [CategoryApiController::class, 'store'])->name('store')->middleware('role:admin');
        Route::put('{id}',    [CategoryApiController::class, 'update'])->name('update')->middleware('role:admin');
        Route::delete('{id}', [CategoryApiController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    // Ventas
    Route::prefix('sales')->name('api.sales.')->group(function () {
        Route::get('summary',  [SaleApiController::class, 'summary'])->name('summary');
        Route::get('/',        [SaleApiController::class, 'index'])->name('index');
        Route::get('{id}',     [SaleApiController::class, 'show'])->name('show');
        Route::delete('{id}',  [SaleApiController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    // Reparaciones
    Route::prefix('repairs')->name('api.repairs.')->group(function () {
        Route::get('/',                   [RepairApiController::class, 'index'])->name('index');
        Route::post('/',                  [RepairApiController::class, 'store'])->name('store');
        Route::get('{id}',                [RepairApiController::class, 'show'])->name('show');
        Route::patch('{id}/status',       [RepairApiController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('{id}',             [RepairApiController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    // Dashboard [admin]
    Route::prefix('dashboard')->name('api.dashboard.')->middleware('role:admin')->group(function () {
        Route::get('summary',      [DashboardApiController::class, 'summary'])->name('summary');
        Route::get('top-products', [DashboardApiController::class, 'topProducts'])->name('top-products');
        Route::get('cash-session', [DashboardApiController::class, 'cashSession'])->name('cash-session');
    });
});
