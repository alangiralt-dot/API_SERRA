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

        $customer = $this->createTestUser('client1@fusteriasaubi.com', false);
        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer);

        Passport::actingAs($admin);

        $payload = [
            'status_id' => 2,
        ];

        $response = $this->patchJson("/api/orders/3/status", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Order status successfully updated.'
        ]);

        $this->assertDatabaseHas('orders', [
            'id'        => 3,
            'status_id' => 2,
        ]);
    }
}
