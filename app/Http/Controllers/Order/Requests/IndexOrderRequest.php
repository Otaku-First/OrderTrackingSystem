<?php

namespace App\Http\Controllers\Order\Requests;


use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class IndexOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => 'integer|nullable',
            'sort_field' => 'in:created_at,updated_at',
            'sort_order' => 'in:asc,desc',
            'filters.*'=>'array',
            'filters.product_name'=>'string|max:100|nullable',
            'filters.status'=>'in:'.implode(',',array_column(OrderStatusEnum::cases(),'name')),
            'filters.amount_min'=>'numeric',
            'filters.amount_max'=>'numeric'
        ];
    }


}


