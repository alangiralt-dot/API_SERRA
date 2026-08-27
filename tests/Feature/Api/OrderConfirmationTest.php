<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ChildProduct;
use App\Models\User;
use App\Models\Status;


class OrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    
    public function test_customer_can_confirm_order_successfully_and_decrements_stock(): void
    {
        $this->setUpPassportPersonalClient();
        $this->createTestUser();
        $this->setUpCatalog();

        $user = \App\Models\User::where('email', 'info@fusteriasaubi.com')->first();
        $payload = [
            'order_lines' => [
                ['id' => 6, 'quantity' => 60],
                ['id' => 132, 'quantity' => 4],
                ['id' => 149, 'quantity' => 9]
            ]
        ];
        
        \Laravel\Passport\Passport::actingAs($user);
        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'order_id' => 1
        ]);

        $this->assertDatabaseHas('orders', [
            'id'                 => 1,
            'customer_id'        => 1,
            'code'               => 'SERRA-2026-00001',
            'status_id'          => 1,
            'order_availability' => 'Consultar',
            'total_amount'       => 2338.60
        ]);

        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 6, 
            'quantity'         => 60,
            'sale_unit_price'  => 6.5100,
            'subtotal'         => 390.60
        ]);
        
        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 132, 
            'quantity'         => 4,
            'sale_unit_price'  => 1920.0000,
            'subtotal'         => 1179.65
        ]);
        
        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 149, 
            'quantity'         => 9,
            'sale_unit_price'  => 16.1100,
            'subtotal'         => 362.48
        ]);

        $this->assertEquals(0, \App\Models\ChildProduct::find(6)->stock);
        $this->assertEquals(56, \App\Models\ChildProduct::find(132)->stock);
        $this->assertEquals(51, \App\Models\ChildProduct::find(149)->stock);
    }
    
    public function test_customer_cannot_confirm_order_if_stock_is_insufficient(): void
    {
        $this->setUpPassportPersonalClient();
        $this->createTestUser();
        $this->setUpCatalog();

        $user = \App\Models\User::where('email', 'info@fusteriasaubi.com')->first();

        $payload = [
            'order_lines' => [
                ['id' => 6, 'quantity' => 75]
            ]
        ];

        \Laravel\Passport\Passport::actingAs($user);
        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'status'   => 'error',
            'message' => 'The stock for product 6 is 60.'
        ]);

        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('child_product_order');

        $this->assertEquals(60, \App\Models\ChildProduct::find(6)->stock);
    }

    public function test_an_administrator_cannot_place_orders_and_receives_forbidden(): void
    {
        $this->setUpPassportPersonalClient();
        $this->createTestUser();
        $this->setUpCatalog();

        $user = \App\Models\User::where('email', 'info@fusteriasaubi.com')->first();
        $user->is_admin = true;
        $user->save();

        $payload = [
            'order_lines' => [
                ['id' => 6, 'quantity' => 60]
            ]
        ];

        \Laravel\Passport\Passport::actingAs($user);
        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(403);
        
        $response->assertJson([
            'status'   => 'error',
            'message' => 'An administrator cannot place orders in the system.'
        ]);

        $this->assertDatabaseEmpty('orders');
    }
}
