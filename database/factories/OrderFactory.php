<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'product_name' => $this->faker->word,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => OrderStatusEnum::cases()[$this->faker->numberBetween(0, 3)],
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
