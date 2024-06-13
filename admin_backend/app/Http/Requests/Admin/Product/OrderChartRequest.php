<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class OrderChartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date_from'  => 'required|date_format:Y-m-d',
            'date_to'    => 'date_format:Y-m-d',
            'type'       => 'required|in:year,month,day',
            'chart'      => 'in:count,price,avg_price,avg_quantity,tax,quantity,total_products_count,products_count',
            'shop_id'    => 'exists:shops,id',
            'column'     => 'regex:/^[a-zA-Z-_]+$/',
            'sort'       => 'string|in:asc,desc',
            'search'     => 'string',
            'product_id' => 'nullable|integer|exists:products,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'productIds'   => 'array'
        ];
    }
}
