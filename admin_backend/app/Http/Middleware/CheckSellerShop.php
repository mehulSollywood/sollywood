<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSellerShop
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return $this->errorResponse('ERROR_403',  trans('errors.' . ResponseError::ERROR_403, [], request()->lang ?? 'en'), Response::HTTP_UNAUTHORIZED);
        }

        if (auth('sanctum')->check()) {
            if (isset(auth('sanctum')->user()->shop) && auth('sanctum')->user()->role == 'seller')
            {
                return $next($request);

            }
            if (isset(auth('sanctum')->user()->moderatorShop) && ((auth('sanctum')->user())->role == 'moderator')
                || ((auth('sanctum')->user())->role == 'deliveryman'))
            {
                return $next($request);
            }
            return $this->errorResponse(
                ResponseError::ERROR_204,
                trans('errors.' . ResponseError::ERROR_204, [], $request->lang),
                Response::HTTP_NOT_FOUND);
        }
        return $this->errorResponse('ERROR_100', trans('errors.' . ResponseError::ERROR_100, [], request()->lang), Response::HTTP_UNAUTHORIZED);

    }
}
