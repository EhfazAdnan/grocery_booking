<?php

namespace App\Providers;

use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Repositories\GroceryItemRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GroceryItemRepositoryInterface::class, GroceryItemRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
