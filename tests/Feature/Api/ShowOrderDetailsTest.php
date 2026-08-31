<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowOrderDetailsTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    
    public function test_user_cannot_view_non_existent_order_details(): void
    {
        $this->setUpPassportPersonalClient();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $response = $this->getJson('/api/orders/99999');

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'The selected id is invalid.'
        ]);
    }
    
    public function test_customer_cannot_view_order_details_of_another_customer(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin     = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer1 = $this->createTestUser('client1@fusteriasaubi.com', false);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer1);
        $this->setUpOrderDetailsLines();

        Passport::actingAs($customer1);

        $response = $this->getJson('/api/orders/1');

        $response->assertStatus(403);
        $response->assertJson([
            "message" => "This action is unauthorized."
        ]);
    }

    public function test_administrator_can_view_any_order_details(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin    = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('client1@fusteriasaubi.com', false);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer);
        $this->setUpOrderDetailsLines();

        Passport::actingAs($admin);

        $response = $this->getJson('/api/orders/3');

        $response->assertStatus(200);
        $response->assertJson([
            'id'                 => 3,
            'code'               => 'SERRA-2026-00003',
            'status'             => 'Confirmada',
            'date'               => '03/07/2026 11:15',
            'order_availability' => 'Consultar',
            'base_imposable'     => 1153.16,
            'iva'                => 242.16,
            'total_amount'       => 1395.32,
            'order_lines'        => [
                [
                    'reference'       => '91687027',
                    'width'           => 160,
                    'height'          => 80,
                    'length'          => 12000,
                    'quantity'        => 2,
                    'sale_unit_price' => 1920.00,
                    'subtotal'        => 589.82
                ],
                [
                    'reference'       => '22499900',
                    'width'           => 95,
                    'height'          => 63,
                    'length'          => 2500,
                    'quantity'        => 20,
                    'sale_unit_price' => 16.11,
                    'subtotal'        => 805.50
                ]
            ]
        ]);
    }
}
