<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Gallery;
use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrandImport implements ToCollection,WithHeadingRow,WithBatchInserts
{
    use Importable;

    /**
     * @param Collection $collection
     * @return mixed
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $brand = Brand::updateOrCreate(['title' => $row['brand_name']],[
                'title' => $row['brand_name'],
            ]);

            if (isset($row['picture'])) {

                $brand->galleries()->delete();

                $images = explode(',', $row['picture']);

                foreach ($images as $image) {
                    if (empty($image)) {
                        continue;
                    }
                    try {
                        Gallery::create([
                            'title' => $image,
                            'path' => $image,
                            'type' => 'brands',
                            'loadable_type' => 'App\Models\Brand',
                            'loadable_id' => $brand->id,
                        ]);

                    } catch (\Throwable $e) {
                        Log::error('failed img upload', [
                            'url' => $image,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }

                $brand->update(['img' => data_get($brand->galleries->first(), 'path')]);
            }

        }
        return true;
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
