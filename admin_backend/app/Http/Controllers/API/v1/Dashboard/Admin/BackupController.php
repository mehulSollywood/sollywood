<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

/**
 * @author  Githubit
 * @email   support@githubit.com
 * @phone   +1 202 340 10-32
 * @site    https://githubit.com/
 */

use App\Http\Requests\FilterParamsRequest;
use App\Traits\ApiResponse;
use Artisan;
use Illuminate\Support\Str;
use App\Models\BackupHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use App\Http\Resources\BackupResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BackupController extends AdminBaseController
{
    use ApiResponse;

    /**
     * Handle the incoming request.
     *
     * @return JsonResponse
     */
    /**
     * Handle the incoming request.
     *
     * @return JsonResponse
     */
    public function download(): JsonResponse
    {
        if (!Storage::exists('public/laravel-backup')) {
            Storage::makeDirectory('public/laravel-backup');
        }

        Artisan::call('backup:run');
//
//        if (!$res) {
//            return $this->onErrorResponse([
//                'code'    => ResponseError::ERROR_400,
//                'message' => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
//            ]);
//        }
//        $artisan = Process::fromShellCommandline("cd ". base_path() . " && php artisan backup:run");
//        $artisan->run();
//
//        if (!$artisan->isSuccessful()) {
//            return $this->onErrorResponse([
//                'code'    => $artisan->isSuccessful(),
//                'message' => $artisan->getErrorOutput(),
//            ]);
//        }

        $path = Storage::disk('public')->path('laravel-backup');

        $files = File::allFiles($path);

        $result = (object)[
            'title' => 'title'
        ];

        foreach ($files as $item) {

            $title = Str::of($item)->after('laravel-backup');

            $result = BackupHistory::updateOrCreate([
                'title' => $title
            ], [
                'status' => true,
                'path' => '/storage/laravel-backup/' . $title,
                'created_by' => auth('sanctum')->id(),
                'created_at' => now(),
            ]);
        }

        return $this->successResponse('Backup was successfully', [
            'title' => $result->title,
            'path' => '/storage/laravel-backup/' . $result->title,
        ]);
    }

    public function histories(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $backups = BackupHistory::with('user')
            ->orderBy($request->input('column', 'id'), $request->input('sort', 'desc'))
            ->paginate($request->input('perPage', 15));

        return BackupResource::collection($backups);
    }
}
