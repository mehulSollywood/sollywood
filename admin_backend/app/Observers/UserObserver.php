<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ProjectService\ProjectService;
use Exception;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class UserObserver
{
    /**
     * Handle the Shop "creating" event.
     *
     * @param User $user
     * @return void
     */
    public function creating(User $user): void
    {
        $user->uuid = Str::uuid();
        $myReferral = Str::random(2) . $user->id . Str::random(2);

        if (Str::length($myReferral) > 8) {
            $myReferral = Str::limit($myReferral, 8);
        } else if (Str::length($myReferral) < 8) {
            $myReferral .= Str::random(8 - Str::length($myReferral));
        }

        $user->uuid         = Str::uuid();
        $user->my_referral  = Str::upper($myReferral);
        $this->projectStatus();
    }

    /**
     * Handle the User "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the User "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function projectStatus(){
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return (new ProjectService())->activationError();
        }
    }
}
