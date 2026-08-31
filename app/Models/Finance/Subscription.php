<?php

namespace App\Models\Finance;

use App\Models\Plan;
use App\Services\Analytics\GoogleTagManager;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $table = 'subscriptions';

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    protected static function booted(): void
    {
        static::updated(static function (self $subscription) {
            if (! $subscription->wasChanged('stripe_status')) {
                return;
            }

            $cancelledStatuses = ['cancelled', 'canceled', 'bank_canceled'];

            if (in_array((string) $subscription->stripe_status, $cancelledStatuses, true)) {
                GoogleTagManager::subscriptionCancelled($subscription);
            }
        });
    }
}
