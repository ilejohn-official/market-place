<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Events\CallInitiated;
use App\Listeners\NotifySellerBookingCreated;
use App\Listeners\NotifyReceiverCallIncoming;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

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
        Event::listen(BookingCreated::class, NotifySellerBookingCreated::class);
        Event::listen(CallInitiated::class, NotifyReceiverCallIncoming::class);
    }
}
