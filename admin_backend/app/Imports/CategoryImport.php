<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToCollection,WithHeadingRow,WithBatchInserts
{
    use Importable;

    /**
     * @param Collection $collection
     * @return mixed
     */
    public function collection(Collection $collection)
    {
        $language = Language::where('default', 1)->first();

        foreach ($collection as $row) {
            $parentCategory = Category::query()
                ->whereHas('parent.translation',fn($q) => $q->where('title',$row['parent_category_name']))
                ->first();

            if (!$parentCategory){
                $parentCategory = Category::create([
                    'parent_id' => null
                ]);

                $parentCategory->translation()->create([
                    'locale' => $language->locale,
                    'title' => $row['parent_category_name'],
                    'description' => null
                ]);
            }

            $category = Category::query()
                ->whereHas('translation',fn($q) => $q->where('title',$row['category_name']))
                ->first();

            if (!$category){
                $category = Category::create([
                    'parent_id' => $parentCategory->id
                ]);

                $category->translation()->create([
                    'locale' => $language->locale,
                    'title' => $row['category_name'],
                    'description' => null
                ]);

            }

            if (isset($row['category_picture'])) {

                $category->galleries()->delete();

                $images = explode(',', $row['category_picture']);

                foreach ($images as $image) {
                    if (empty($image)) {
                        continue;
                    }
                    try {
                        Gallery::create([
                            'title' => $image,
                            'path' => $image,
                            'type' => 'categories',
                            'loadable_type' => 'App\Models\Category',
                            'loadable_id' => $category->id,
                        ]);

                    } catch (\Throwable $e) {
                        Log::error('failed img upload', [
                            'url' => $image,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }

                $category->update(['img' => data_get($category->galleries->first(), 'path')]);
            }

        }
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string'],
            'parent_category_name' => ['required', 'string'],
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
