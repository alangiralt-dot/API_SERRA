<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_administrator_can_grant_admin_privileges_to_a_client(): void
    {
        $this->setUpPassportPersonalClient();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        $customer = $this->createTestUser('joan.ferrer@test.com', false);

        Passport::actingAs($admin);

        $response = $this->patchJson("/api/customers/{$customer->customer_id}/roles", [
            'is_admin' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'User role successfully updated.'
        ]);

        $this->assertDatabaseHas('users', [
            'id'       => $customer->id,
            'is_admin' => true,
        ]);
    }

    public function test_administrator_can_revoke_admin_privileges_from_another_admin(): void
    {
        $this->setUpPassportPersonalClient();

        $admin1 = $this->createTestUser('admin1@fusteriasaubi.com', true);
        $admin2 = $this->createTestUser('admin2@fusteriasaubi.com', true);

        Passport::actingAs($admin1);

        $response = $this->patchJson("/api/customers/{$admin2->customer_id}/roles", [
            'is_admin' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'User role successfully updated.'
        ]);

        $this->assertDatabaseHas('users', [
            'id'       => $admin2->id,
            'is_admin' => false,
        ]);
    }
}
