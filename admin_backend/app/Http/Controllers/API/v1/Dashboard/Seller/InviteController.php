<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\InviteResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InviteController extends SellerBaseController
{

    public function __construct(protected Invitation $model)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $invites = $this->model->filter($request->all())->with([
            'user.roles',
            'user' => function ($q) {
                $q->select('id', 'firstname', 'lastname');
            },
            'shop.translation'
        ])
            ->where('shop_id', $this->shop->id)->orderBy($request->column ?? 'id', $request->sort ?? 'desc')->paginate($request->perPage ?? 15);
        return InviteResource::collection($invites);

    }

    public function changeStatus(int $id): InviteResource|JsonResponse
    {
        $invite = $this->model->firstWhere(['id' => $id, 'shop_id' => $this->shop->id]);
        if ($invite) {
            if (isset(request()->role) && (request()->role == 'moderator' || request()->role == 'deliveryman')) {
                $invite->update(['status' => Invitation::STATUS['excepted'], 'role' => request()->role]);
                $invite->user->syncRoles(request()->role);
            } else {
                $invite->update(['status' => Invitation::STATUS['rejected'], 'role' => 'user']);
                $invite->user->syncRoles('user');
            }
            return InviteResource::make($invite);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
