<?php

namespace App\Exceptions;

use App;
use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use GuzzleHttp\Client;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     *
     *
     * @throws Throwable
     */

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        return $this->handleException($request, $exception);
    }



    public function handleException($request, Throwable $exception)
    {
        if ($exception instanceof RouteNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->errorResponse(ResponseError::ERROR_406,
                __('errors.' . ResponseError::ERROR_406, [], request('lang')),
                Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->errorResponse(ResponseError::ERROR_404,
                __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof ValidationException) {

            $items = $exception->validator->errors()->getMessages();

            return $this->requestErrorResponse(
                ResponseError::ERROR_400,
                trans('errors.' . ResponseError::ERROR_400, [], request()->lang),
                $items, Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof AuthenticationException) {
            info('auth_token', [$request->header('authorization')]);
            return $this->errorResponse(ResponseError::ERROR_401,
                __('errors.' . ResponseError::ERROR_401, [], request('lang')),
                Response::HTTP_UNAUTHORIZED);
        }

        if(method_exists($exception, 'getStatusCode') && $exception->getStatusCode() == 429){
//            if (\App::environment(['production'])) {
//                $this->sendDDosAttack($exception);
//            }
            return $this->errorResponse(ResponseError::ERROR_429, 'errors.' . ResponseError::ERROR_429,Response::HTTP_TOO_MANY_REQUESTS);
        }


//        if ($exception instanceof AuthorizationException) {
//            return $this->errorResponse(ResponseError::ERROR_403,
//                __('errors.' . ResponseError::ERROR_403, [], request('lang')),
//                Response::HTTP_UNAUTHORIZED);
//            return response()->errorJson($exception->getMessage(), 403);
//        }


        return $this->errorResponse(500,$exception->getMessage().' in '.$exception->getFile().":".$exception->getLine(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }





}
