<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\Shop;
use App\Models\ShopList;
use App\Models\User;
use App\Models\Invitation;
use App\Helpers\ResponseError;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopResource;
use App\Http\Resources\ShopListResource;
use App\Http\Resources\ShowShopListResource;
use App\Http\Requests\User\Shop\StoreRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Interfaces\ShopServiceInterface;
use App\Repositories\Interfaces\ShopRepoInterface;

class ShopController extends UserBaseController
{
    public function __construct(protected ShopRepoInterface $shopRepository,protected ShopServiceInterface $shopService)
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse|
     */
    public function store(StoreRequest $request): JsonResponse
    {
       
        /** @var User $user */
        $collection = $request->validated();
       // dd($collection);
        $user = auth('sanctum')->user();
        $shop = Shop::where('user_id',$user->id)->first();
        

        if (!$shop){
            $result = $this->shopService->create($collection);
           // dd($result);
            Invitation::create([
                'shop_id' => $result['data']->id,
                'user_id' => $user?->id,
                'role' => User::ROLE_SELLER,
                'status' => Invitation::STATUS_NEW,
            ]);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), ShopResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_205, __('errors.' . ResponseError::ERROR_205, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function shoplist(Request $request): JsonResponse
    {
        $user = User::where('uuid', $request->uuid)->pluck('id')->first();
        $userShop = Shop::where('user_id',$user)->get(['id','slug'])->toArray();
        return $this->successResponse("fetch referral user succesfully",['data'=>$userShop]);
    }

    public function showShopList(): JsonResponse
    {
        $shoplist = ShopList::all();
        $userIds = $shoplist->pluck('user_id')->toArray();
        $users = User::whereIn('id', $userIds)->get();
        
        if ($shoplist->isEmpty() || $users->isEmpty()) {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
    
        // Transform the User models into UserResource
        $userResources = ShopListResource::collection($users);
    
        // Assuming you want to return both shop list and associated users
        return $this->successResponse(__('web.events_found'), [
            //'shop_list' => ShopListResource::collection($shoplist),
            'users' => $userResources,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */

   public function getshoplist(int $id): JsonResponse
{
    // Retrieve ShopList records based on the $id parameter
    $shoplist = ShopList::where('user_id', $id)->get(['id','slug']);
    // Check if the $shoplist is empty
    if ($shoplist->isEmpty()) {
        return $this->errorResponse(
            ResponseError::ERROR_404,
            trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    // Retrieve user IDs from $shoplist
   // $userIds = $shoplist->pluck('user_id')->toArray();

    // Retrieve users based on the user IDs
   // $users = User::whereIn('id', $userIds)->get();

    // Check if $users is empty
    // if ($users->isEmpty()) {
    //     return $this->errorResponse(
    //         ResponseError::ERROR_404,
    //         trans('errors.' . ResponseError::ERROR_404, [], $this->language),
    //         Response::HTTP_NOT_FOUND
    //     );
    // }

    // Transform the User models into UserResource if needed
    $userResources = ShowShopListResource::collection($shoplist);

    // Assuming you want to return both shop list and associated users
    return $this->successResponse(__('web.events_found'), [
        'shop_list' => ShowShopListResource::collection($shoplist),
        //'users' => $userResources, // Changed key to 'users' for clarity
    ]);
}


}
