<?php
namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Blog;
use App\Models\Cart;
use App\Models\DeliveryManSetting;
use App\Models\EmailSubscription;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\PointHistory;
use App\Models\Recipe;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Shop;
use App\Models\SocialProvider;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPoint;
use App\Models\Wallet;
use App\Services\CoreService;
use App\Services\Interfaces\UserServiceInterface;
use App\Models\User as Model;
use Exception;
use Throwable;

class UserService extends CoreService implements UserServiceInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Model::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        try {
            $user = $this->model()->create(
                $this->setUserParams($collection) + [
                    'password' => bcrypt($collection->password),
                    'ip_address' =>  request()->ip()
                ]);

            if (isset($collection->images)) {
                $user->uploads($collection->images);
                $user->update(['img' => $collection->images[0]]);
            }

            $user->syncRoles(data_get($collection, 'role', 'user'));

            if($user->hasRole(['moderator', 'deliveryman', 'waiter', 'cook']) && is_array(data_get($collection, 'shop_id'))) {

                foreach (data_get($collection, 'shop_id') as $shopId) {

                    $user->invitations()->withTrashed()->updateOrCreate([
                        'shop_id' => $shopId,
                    ], [
                        'deleted_at' => null
                    ]);
                }

            }
            (new UserWalletService())->create($user);

            $user->emailSubscription()->updateOrCreate([
                'user_id' => $user->id
            ], [
                'active' => true
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update(string $uuid, $collection): array
    {
        $user = $this->model()->firstWhere('uuid', $uuid);
        if ($user) {
            try {
                $item = $user->update($this->setUserParams($collection, $user));
                if (isset($collection->password)) {
                    $user->update([
                        'password' => bcrypt($collection->password)
                    ]);
                }
                if ($item && isset($collection->images)) {
                    $user->galleries()->delete();
                    $user->update(['img' => $collection->images[0]]);
                    $user->uploads($collection->images);
                }

                if (data_get($collection, 'subscribe') !== null) {

                    $user->emailSubscription()->updateOrCreate([
                        'user_id' => $user->id
                    ], [
                        'active' => !!data_get($collection, 'subscribe')
                    ]);
                }
                if($user->hasRole(['moderator', 'deliveryman']) && is_array(data_get($collection, 'shop_id'))) {
                    $user->invitations()->delete();
                    foreach (data_get($collection, 'shop_id') as $shopId) {

                        $user->invitations()->withTrashed()->updateOrCreate([
                            'shop_id' => $shopId,
                        ], [
                            'role' => $user->role,
                            'status' => 3,
                            'deleted_at' => null
                        ]);
                    }
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
            } catch (Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function updatePassword($uuid, $collection): array
    {
        $user = $this->model()->firstWhere('uuid', $uuid);
        if ($user) {
            try {
                $user->update(['password' => bcrypt($collection['password'])]);
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
            } catch (Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function updatePasswordWithPhone($collection): array
    {
       
      
        $user = $this->model()->where('phone','like',"%".$collection['phone']."%")->first();
        
        if ($user) {
            try {
                $user->update(['password' => bcrypt($collection['password'])]);
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
            } catch (Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param int $id
     * @param $collection
     * @return array
     */
    public function createReview(int $id, $collection): array
    {
        $deliveryMan = $this->model()->find($id);
        if ($deliveryMan){
            $deliveryMan->addReview($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryMan];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function destroy(array $ids): array
    {
        foreach ($ids as $id){
            Blog::where('user_id',$id)?->delete();
            Cart::where('owner_id',$id)?->delete();
            DeliveryManSetting::where('user_id',$id)?->delete();
            EmailSubscription::where('user_id',$id)?->delete();
            Invitation::where('user_id',$id)?->delete();
            Order::where('user_id',$id)?->delete();
            Payout::where('created_by',$id)?->delete();
            PointHistory::where('user_id',$id)?->delete();
            Recipe::where('user_id',$id)?->delete();
            Refund::where('user_id',$id)?->delete();
            Review::where('user_id',$id)?->delete();
            Shop::where('user_id',$id)?->delete();
            SocialProvider::where('user_id',$id)?->delete();
            Ticket::where('user_id',$id)?->delete();
            Transaction::where('user_id',$id)?->delete();
            UserAddress::where('user_id',$id)?->delete();
            UserPoint::where('user_id',$id)?->delete();
            User::where('id',$id)?->delete();
            Wallet::where('user_id',$id)?->delete();
            PaymentProcess::where('user_id',$id)?->delete();
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    public function deleteWorkers($id): array
    {
        $user = User::where('id',$id)->first();
        if ($user){

            if ($user->role == 'seller'){
                if($user->shop){
                    return ['status' => false, 'code' => ResponseError::ERROR_434];
                }
                $user->delete();
            }
            if ($user->role == 'deliveryman'){
                if($user->deliverymanOrders->isNotEmpty()){
                    return ['status' => false, 'code' => ResponseError::ERROR_435];
                }
                $user->delete();
            }

            $user->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function updateNotifications(array $data): array
    {
        try {
            /** @var User $user */
            $user = auth('sanctum')->user();

            $user->notifications()->sync(data_get($data, 'notifications'));

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $user->loadMissing('notifications')
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_502,
                'message' => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
            ];
        }
    }

    public function setUserParams($collection, $user = null): array
    {
        return [
            'firstname' =>  isset($user) ? $collection->firstname ?? $user->firstname : $collection->firstname ?? null,
            'lastname' =>   isset($user) ? $collection->lastname ?? $user->lastname : $collection->lastname ?? null,
            'email' =>      isset($user) ? $collection->email ?? $user->email : $collection->email ?? null,
            'phone' =>      isset($user) ? $collection->phone ?? $user->phone : $collection->phone ?? null,
            'birthday' =>   isset($user) ? $collection->birthday ?? $user->birthday : $collection->birthday ?? null,
            'gender' =>     isset($user) ? $collection->gender ?? $user->gender : $collection->gender ?? null  ?? 'male',
            'firebase_token' =>  isset($user) ? $collection->firebase_token ?? $user->firebase_token : $collection->firebase_token ?? null,
        ];
    }

}
