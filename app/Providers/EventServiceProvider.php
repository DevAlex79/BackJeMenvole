<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;

class EventServiceProvider
{
    public function boot()
    {
        Order::observe(OrderObserver::class);

        Product::observe(ProductObserver::class);
    }
}
