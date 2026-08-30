<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Status;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_administrator_can_update_an_order_status(): void
    {
        $this->setUpPassportPersonalClient();

        $statusConfirmada   = Status::create(['id' => 1, 'status' => 'Confirmada']);
        $statusEnPreparacio = Status::create(['id' => 2, 'status' => 'En preparació']);

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $order = Order::create([
            'customer_id'        => $admin->customer_id,
            'code'               => 'SERRA-2026-00001',
            'status_id'          => $statusConfirmada->id,
            'date'               => now(),
            'order_availability' => '24/48h',
            'total_amount'       => 150.00
        ]);

        $payload = [
            'status_id' => $statusEnPreparacio->id,
        ];

        $response = $this->patchJson("/api/orders/{$order->id}/status", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Order status successfully updated.'
        ]);

        $this->assertDatabaseHas('orders', [
            'id'        => $order->id,
            'status_id' => $statusEnPreparacio->id,
        ]);
    }
}
