<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends AdminBaseController
{
    use ApiResponse;
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return $this->successResponse(__('web.roles_list'), Role::all());
    }
}
