<?php

namespace App\Providers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            $walletAmount = 0;

            if (Auth::check()) {
                $wallet = Wallet::where('user_id', Auth::id())->first();
                $walletAmount = $wallet ? $wallet->amount : 0;
            }
            $view->with('walletAmount', $walletAmount);
        });
    }
}
