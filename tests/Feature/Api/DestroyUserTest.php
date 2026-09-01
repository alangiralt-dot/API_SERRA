<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Customer;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyUserTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_authenticated_user_without_orders_is_completely_purged(): void
    {
        $this->setUpPassportPersonalClient();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('joan.ferrer@test.com', false);
        
        $this->setUpOrdersWithoutDetails($admin, $admin, $admin);

        Passport::actingAs($customer);

        $response = $this->deleteJson('/api/customers/users');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Account and access successfully removed.'
        ]);

        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseMissing('customers', ['id' => $customer->customer_id]);
        $this->assertDatabaseMissing('orders', ['customer_id' => $customer->customer_id]);
    }

    public function test_authenticated_user_with_orders_keeps_customer_row_intact(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('joan.ferrer@test.com', false);

        $this->setUpOrdersWithoutDetails($admin, $admin, $customer);

        Passport::actingAs($customer);

        $response = $this->deleteJson('/api/customers/users');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Account and access successfully removed.'
        ]);

        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->customer_id]);
        $this->assertDatabaseHas('orders', ['customer_id' => $customer->customer_id]);
    }
}
