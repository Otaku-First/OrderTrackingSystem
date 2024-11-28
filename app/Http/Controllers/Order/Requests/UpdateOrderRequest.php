<?php

namespace App\Http\Controllers\Order\Requests;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_name'=>'string|max:100',
            'amount'=>'numeric',
            'status'=>'in:'.implode(',',array_column(OrderStatusEnum::cases(),'name')),
        ];
    }
}
