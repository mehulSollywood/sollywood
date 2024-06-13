<?php

namespace App\Traits;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait Loadable
{

    public function uploads($images)
    {
        foreach ($images as $index => $file) {

            $imagePath = config('app.img_host') . "$file";

            if (!strpos($file,config('app.img_host'))){
                $imagePath = $file;
            }

            $title = Str::of($file)->after('/');
            $type = Str::of($file)->before('/');

            $image = new Gallery();
            $image->title = $title;
            $image->path = $imagePath;
            $image->type = $type ?? 'main';
            $this->galleries()->save($image);
        }
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'loadable');
    }
}

