<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $role
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function handle(Request $request, Closure $next, $role)
    {

        $roles = is_array($role) ? $role : explode('|', $role);
        if (auth('sanctum')->user())
        {
            if (auth('sanctum')->user()->hasAnyRole($roles)) {
            if (!cache()->has('project.status') || cache('project.status')->active != 1){
                return $this->errorResponse('ERROR_403',  trans('errors.' . ResponseError::ERROR_403, [], request()->lang ?? 'en'), Response::HTTP_UNAUTHORIZED);
            }
                return $next($request);
            }
            return $this->errorResponse('ERROR_101',   trans('errors.' . ResponseError::ERROR_101, [], request()->lang), Response::HTTP_FORBIDDEN);
        }
        return $this->errorResponse('ERROR_100',  trans('errors.' . ResponseError::ERROR_100, [], request()->lang), Response::HTTP_UNAUTHORIZED);

    }
}
