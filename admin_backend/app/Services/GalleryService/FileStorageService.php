<?php

namespace App\Services\GalleryService;

use App\Helpers\ResponseError;
use App\Models\Gallery;
use App\Services\CoreService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService extends CoreService
{

    protected function getModelClass(): string
    {
        return Gallery::class;
    }

    public function getStorageFiles($type, $length = null, $start = null)
    {
        return Gallery::where('type', $type)->skip($start)->take($length)->orderBy('id','desc')->get();
    }

    public function deleteFileFromStorage($file): array
    {
        $path = Str::of($file)->after('storage');

        $gallery = Gallery::firstWhere('path',$file);

        if($gallery){

            $gallery->delete();

            $data = Storage::disk('public')->exists($path);

            if ($data){
                Storage::disk('public')->delete($path);
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
