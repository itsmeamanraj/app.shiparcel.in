<?php

use App\Helpers\EkartApiService;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WareHouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// routes/web.php


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    /**Warehouse creation */
    Route::get('warehouse/create', [WareHouseController::class, 'show'])->name('create-warehouse');
    Route::post('/create-warehouse', [WareHouseController::class, 'create'])->name('create.warehouse');

    /**Order Creation */
    Route::get('order/create', [OrderController::class, 'show'])->name('create-order');
    Route::post('create-order', [OrderController::class, 'create'])->name('create.order');
    Route::get('list-order', [OrderController::class, 'list'])->name('list.order');
    Route::get('/orders/{id}', [OrderController::class, 'view'])->name('orders.view');
    Route::post('cancel-order', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('order-label-data', [OrderController::class, 'orderLabelData'])->name('order.label-data');



    /**Wallet */
    Route::get('wallet', [WalletController::class, 'show'])->name('wallet');
    Route::get('/wallet/fetchRates', [WalletController::class, 'fetchRates'])->name('wallet.fetchRates');
    Route::post('/wallet/store', [WalletController::class, 'store'])->name('wallet.store');
});


/** User Authentication */
Route::get('/login', [UserAuthController::class, 'login'])->name('admin-login');
Route::post('/user-login', [UserAuthController::class, 'userLogin'])->name('custom.login.submit');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

Route::get('/generate-token/aaa', function () {
    return EkartApiService::getBearerToken();
});
