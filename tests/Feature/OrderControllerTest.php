<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_create_order()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/orders', [
                'product_name' => 'Test Product',
                'amount' => 100.50,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'product_name',
                'amount',
                'status',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('orders', [
            'product_name' => 'Test Product',
            'amount' => 100.50,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_get_their_orders()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        Order::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'product_name', 'amount', 'status']]]);
    }

    public function test_user_can_update_order()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/orders/{$order->id}", [
                'product_name' => 'Updated Product',
                'amount' => 200,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'product_name' => 'Updated Product',
                'amount' => 200,
            ]);

        $this->assertDatabaseHas('orders', [
            'product_name' => 'Updated Product',
            'amount' => 200,
        ]);
    }

    public function test_user_can_delete_order()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Замовлення успішно видалено',
            ]);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }
}
