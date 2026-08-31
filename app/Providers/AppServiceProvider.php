<?php

namespace App\Providers;

use App\Contracts\Repositories\CustomerProfileRepositoryInterface;
use App\Contracts\Repositories\GroceryItemRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Repositories\CustomerProfileRepository;
use App\Repositories\GroceryItemRepository;
use App\Repositories\OrderItemRepository;
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
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(CustomerProfileRepositoryInterface::class, CustomerProfileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
