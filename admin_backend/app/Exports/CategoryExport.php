<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryExport extends BaseExport implements FromCollection, WithHeadings
{

    /**
     * @return Collection
     */
    public function collection()
    {
        $model = Category::with([
        'translation',
        'parent.translation'
        ])->where('parent_id','!=',null)->get();
        return $model->map(function ($model){
            return $this->tableBody($model);
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Parent Category Name',
            'Category Name',
            'Category Picture',
            'Status',
        ];
    }

    private function tableBody($item): array
    {
        $categoryImg = '';

        if (isset($item->galleries))
        {
            $categoryImg = $this->imageUrl($item->galleries);
        }

        return [
            'id' => $item->id,
            'Parent Category Name' => $item->parent?->translation?->title,
            'Category Name' => $item->translation?->title,
            'Category Picture' => $categoryImg,
            'Status' => $item->active ? 'active' : 'inactive',
        ];
    }
}
