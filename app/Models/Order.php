<?php

namespace App\Models;

use App\Events\OrderStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    use HasFactory;
    protected $fillable = [
      'user_id',
      'product_name',
      'amount',
      'status',
    ];


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function (Order $order) {
            if ($order->isDirty('status')) {
                event(new OrderStatusChanged($order, $order->getOriginal('status'), $order->status));
            }
        });
    }

}
