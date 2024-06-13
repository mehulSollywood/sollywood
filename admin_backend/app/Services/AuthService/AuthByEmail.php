<?php

namespace App\Services\AuthService;

use App\Events\Mails\SendEmailVerification;
use App\Models\User;
use App\Services\CoreService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class AuthByEmail extends CoreService
{

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    public function authentication(array $array): JsonResponse
    {
    
        /** @var User $user */

        

        $user = $this->model()
            ->withTrashed()
            ->firstOrCreate([
                'email' => data_get($array, 'email')
            ], [
                'firstname'     => data_get($array, 'firstname','firstname'),
                'birthday'     => data_get($array, 'birthday'),
                'email'         => data_get($array, 'email'),
                'referral'      => data_get($array, 'referral',''),
                'password'      => bcrypt(data_get($array, 'password')),
                'ip_address'    => request()->ip(), 
                'deleted_at'    => null
            ]);

        if (!$user->hasAnyRole(Role::query()->pluck('name')->toArray())) {
            $user->syncRoles('user');
        }

        event(new SendEmailVerification($user));

        if(empty($user->wallet)) {
            (new UserWalletService)->create($user);
        }

        return $this->successResponse('User send email', []);

    }

}
