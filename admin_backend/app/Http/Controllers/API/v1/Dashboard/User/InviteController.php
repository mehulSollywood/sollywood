<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\Shop;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\InviteResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InviteController extends UserBaseController
{

    public function __construct(protected Invitation $model)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $invites = $this->model->filter($request->all())->with([
            'user.roles',
            'user' => function($q) {
                $q->select('id', 'firstname', 'lastname');
            },
            'shop.translation'
        ])
            ->where('user_id', auth('sanctum')->id())
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);
        return InviteResource::collection($invites);
    }


    public function create($shop): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        $shop = Shop::firstWhere('uuid', $shop);
        if ($shop){
            $invite = $this->model->updateOrCreate(['user_id' => $user->id],[
                'shop_id' => $shop->id,
            ]);
            return $this->successResponse(__('web.invite_send'), InviteResource::make($invite));
        }else{
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }

    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $invitation = Invitation::find($id);

        $shop = Shop::find($invitation?->shop_id);

        $shop?->delete();

        $invitation?->delete();

        return $this->successResponse(
            __('errors.' . ResponseError::SUCCESS, locale: $this->language)
        );
    }
}
