<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ProjectController extends AdminBaseController
{

    /**
     * @throws Exception
     */
    public function projectUpload(Request $request): JsonResponse
    {
        $file = $request->file('file') ?? null;
        $destinationPath = base_path();
        $file_name = 'gshop.zip';

        if (!isset($file)) {
            return $this->errorResponse(ResponseError::ERROR_413, __('web.undefined_file_type'));
        }

        try {
            $file->move($destinationPath, $file_name);
            return $this->successResponse(__('web.file_uploaded'), ['title' => $file_name]);
        } catch (Exception $exception){
            return $this->errorResponse(ResponseError::ERROR_501, $exception->getMessage());
        }
    }

    public function projectUpdate(): JsonResponse
    {
        try {
            Artisan::call('project:update');
            return $this->successResponse(__('web.project_has_been_successfully_updated'), []);
        } catch (Exception $exception){
            return $this->errorResponse(ResponseError::ERROR_502, $exception->getMessage());
        }
    }
}
