<?php

namespace App\Http\Controllers\Order\Requests;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class ChangeOrderStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status'=>'in:'.implode(',',array_column(OrderStatusEnum::cases(),'name')),
        ];
    }
}
