<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Events\CallInitiated;
use App\Events\EscrowCreated;
use App\Events\FundsReleased;
use App\Events\WorkMarkedComplete;
use App\Listeners\NotifySellerBookingCreated;
use App\Listeners\NotifyReceiverCallIncoming;
use App\Listeners\NotifyPartsEscrowCreated;
use App\Listeners\NotifyBuyerWorkComplete;
use App\Listeners\NotifySellerFundsReleased;
use App\Contracts\PaymentGatewayInterface;
use App\Payments\PaystackPaymentGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, PaystackPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingCreated::class, NotifySellerBookingCreated::class);
        Event::listen(CallInitiated::class, NotifyReceiverCallIncoming::class);
        Event::listen(EscrowCreated::class, NotifyPartsEscrowCreated::class);
        Event::listen(WorkMarkedComplete::class, NotifyBuyerWorkComplete::class);
        Event::listen(FundsReleased::class, NotifySellerFundsReleased::class);
    }
}
