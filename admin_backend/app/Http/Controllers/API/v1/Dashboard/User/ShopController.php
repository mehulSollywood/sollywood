<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\Shop;
use App\Models\User;
use App\Models\Invitation;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopResource;
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
}
