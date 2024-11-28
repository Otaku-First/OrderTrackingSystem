<?php

namespace App\Http\Controllers\Order\Requests;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_name'=>'string|required|max:100',
            'amount'=>'numeric|required',
        ];
    }
}
