<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Http\Resources\GroupResource;
use App\Repositories\GroupRepository\GroupRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupController extends RestBaseController
{
    public function __construct(protected GroupRepository $groupRepository)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $groups = $this->groupRepository->paginate($request->perPage ?? 15, true);
        return GroupResource::collection($groups);
    }

}
