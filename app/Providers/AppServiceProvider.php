<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Product;

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
        View::composer('layouts.admin', function ($view) {
            $notif_products = Product::with(['supplier'])
                ->whereNotNull('supplier_id')
                ->orderByDesc('created_at')
                ->take(10)
                ->get();
        
            // Jumlah notifikasi BELUM DILIHAT
            $notif_unread_count = Product::whereNotNull('supplier_id')
                ->where('notif_admin_seen', 0)
                ->count();
        
            $view->with('notif_products', $notif_products)
                 ->with('notif_unread_count', $notif_unread_count);
        });

    }
}
