<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowOrdersTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_administrator_can_view_all_workshop_orders(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin    = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('client1@fusteriasaubi.com', false);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer);
        $this->setUpOrderDetailsLines();

        Passport::actingAs($admin);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertExactJson([
            [
                'id'                 => 3,
                'customer_id'        => $customer->customer_id,
                'code'               => 'SERRA-2026-00003',
                'status'             => 'Confirmada',
                'date'               => '03/07/2026 11:15',
                'order_availability' => 'Consultar',
                'total_amount'       => 1395.32
            ],
            [
                'id'                 => 2,
                'customer_id'        => $admin->customer_id,
                'code'               => 'SERRA-2026-00002',
                'status'             => 'Lliurada',
                'date'               => '28/06/2026 15:45',
                'order_availability' => '3/5 dies',
                'total_amount'       => 695.70
            ],
            [
                'id'                 => 1,
                'customer_id'        => $admin->customer_id,
                'code'               => 'SERRA-2026-00001',
                'status'             => 'Lliurada',
                'date'               => '10/06/2026 10:30',
                'order_availability' => '24/48h',
                'total_amount'       => 785.12
            ]
        ]);
    }


    public function test_customer_can_only_view_their_own_personal_orders(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin    = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('client1@fusteriasaubi.com', false);

        $this->setUpOrdersWithoutDetails($admin, $customer, $customer);
        $this->setUpOrderDetailsLines();

        Passport::actingAs($customer);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertExactJson([
            [
                'id'                 => 3,
                'customer_id'        => $customer->customer_id,
                'code'               => 'SERRA-2026-00003',
                'status'             => 'Confirmada',
                'date'               => '03/07/2026 11:15',
                'order_availability' => 'Consultar',
                'total_amount'       => 1395.32
            ],
            [
                'id'                 => 2,
                'customer_id'        => $customer->customer_id,
                'code'               => 'SERRA-2026-00002',
                'status'             => 'Lliurada',
                'date'               => '28/06/2026 15:45',
                'order_availability' => '3/5 dies',
                'total_amount'       => 695.70
            ]
        ]);
    }

    public function test_new_customer_without_orders_receives_validation_error(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin      = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer   = $this->createTestUser('client1@fusteriasaubi.com', false);
        $newCustomer = $this->createTestUser('clientnou@fusteriasaubi.com', false);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer);
        $this->setUpOrderDetailsLines();

        Passport::actingAs($newCustomer);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'The selected customer id is invalid.'
        ]);
    }
}
