<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnPayReferral implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User|null $user
     */
    public function __construct(public ?User $user)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $priceFrom = data_get(Settings::adminSettings()->where('key', 'referral_price_from')->first(), 'value');
        $priceTo   = data_get(Settings::adminSettings()->where('key', 'referral_price_to')->first(), 'value');

        if (
            !empty($this->user) && ( !empty($priceFrom) || !empty($priceTo) ) &&
            !empty(data_get($this->user, 'referral')) &&
            $this->user->orders->where('status', Order::DELIVERED)->count() === 1
        ) {
            $owner = User::where('my_referral', $this->user->referral)->first();

            if (!empty($owner) && $owner->wallet && (double)$priceFrom > 0) {
                $owner->wallet->decrement('price', (double)$priceFrom);
            }

            if ($this->user->wallet && (double)$priceTo > 0) {
                $this->user->wallet->decrement('price', (double)$priceTo);
            }

        }
    }
}
