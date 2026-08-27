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

    private function setUpCatalog(): void
    {
        $this->setUpPassportPersonalClient();
        $this->createTestUser();
        
        $category = \App\Models\Category::create([
            'id' => 1,
            'category' => 'Fusta',
            'father_id' => null
        ]);

        $fatherProduct = \App\Models\FatherProduct::create([
            'id'          => 1, 
            'name'        => 'Producte Pare Test', 
            'image_path'  => 'images/test.png',
            'category_id' => $category->id
        ]);

        $availability1 = \App\Models\Availability::create(['id' => 1, 'availability' => '24/48h', 'delay_weight' => 10]);
        $availability2 = \App\Models\Availability::create(['id' => 2, 'availability' => '3/5 dies', 'delay_weight' => 20]);
        $availability3 = \App\Models\Availability::create(['id' => 3, 'availability' => 'Consultar', 'delay_weight' => 30]);

        \App\Models\Unit::create(['id' => 1, 'unit' => '€ / tira']);
        \App\Models\Unit::create(['id' => 2, 'unit' => '€ / metre']);
        \App\Models\Unit::create(['id' => 3, 'unit' => '€ / m3']);

        $product6 = ChildProduct::create([
            'id'                 => 6, 
            'reference'          => '90164000', 
            'width'              => 35, 
            'height'             => -1,
            'length'             => 2500, 
            'cost_unit_price'    => 3.1000, 
            'current_unit_price' => 6.5100,
            'pack'               => 15,
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 1, 
            'unit_id'            => 1
        ]);
        
        $product132 = ChildProduct::create([
            'id'                 => 132, 
            'reference'          => '91687027', 
            'width'              => 160, 
            'height'             => 80, 
            'length'             => 12000, 
            'cost_unit_price'    => 914.2857, 
            'current_unit_price' => 1920.0000, 
            'pack'               => 1, 
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 2, 
            'unit_id'            => 3
        ]);
        
        $product149 = ChildProduct::create([
            'id'                 => 149, 
            'reference'          => '22499900', 
            'width'              => 95, 
            'height'             => 63, 
            'length'             => 2500, 
            'cost_unit_price'    => 7.6714, 
            'current_unit_price' => 16.1100, 
            'pack'               => 1, 
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 3, 
            'unit_id'            => 2
        ]);

        \App\Models\Status::create(['id' => 1, 'status' => 'Confirmada']);
    }
    
    public function test_customer_can_confirm_order_successfully_and_decrements_stock(): void
    {
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
            'message' => 'The stock for product 6 is 60.'
        ]);

        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('child_product_order');

        $this->assertEquals(60, \App\Models\ChildProduct::find(6)->stock);
    }

    public function test_an_administrator_cannot_place_orders_and_receives_forbidden(): void
    {
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
            'error'   => 'Forbidden',
            'message' => 'An administrator cannot place orders in the system.'
        ]);

        $this->assertDatabaseEmpty('orders');
    }
}
