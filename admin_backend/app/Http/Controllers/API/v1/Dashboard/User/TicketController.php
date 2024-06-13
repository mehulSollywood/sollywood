<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TicketResource;
use App\Services\TicketService\TicketService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\TicketRepository\TicketRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends UserBaseController
{
    public function __construct(protected TicketRepository $ticketRepository,protected TicketService $ticketService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $categories = $this->ticketRepository->paginate(
            $request->perPage ?? 15, $request->merge(['created_by' => auth('sanctum')->id()])->all()
        );
        return TicketResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->ticketService->create($request->merge(['created_by' => auth('sanctum')->id()]));
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), TicketResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketRepository->ticketDetails($id);
        if ($ticket && $ticket->created_by == auth('sanctum')->id()){
            return $this->successResponse(ResponseError::NO_ERROR, TicketResource::make($ticket));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->ticketService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), TicketResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

}
