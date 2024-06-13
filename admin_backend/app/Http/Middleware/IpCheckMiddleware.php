<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpCheckMiddleware
{
    use ApiResponse;

    protected array $whiteList = [
        '206.54.191.37'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (empty($this->whiteList)) {
            return $next($request);
        }
        $addr = $_SERVER['REMOTE_ADDR'];

        Log::info('TEST', [$addr,$this->whiteList]);

        if (in_array($addr, $this->whiteList)) {
            return $next($request);
        }

        return $this->errorResponse(
            ResponseError::ERROR_400,
            'null',
            Response::HTTP_UNAUTHORIZED
        );
    }
}
