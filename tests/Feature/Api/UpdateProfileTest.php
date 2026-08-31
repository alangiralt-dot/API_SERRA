<?php

namespace Tests\Feature\Api;

use App\Models\City;
use App\Models\Province;
use App\Models\Customer;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_authenticated_user_can_update_profile_with_new_city(): void
    {
        $this->setUpPassportPersonalClient();

        $user = $this->createTestUser('info@fusteriasaubi.com', false);

        Passport::actingAs($user);

        $response = $this->putJson('/api/customers/profiles', [
            'first_name'     => 'Carles Modified',
            'last_name'      => 'Saubí Modified',
            'phone'          => '972230680',
            'street'         => 'Carrer Nou',
            'address_number' => '20',
            'address_floor'  => '1r',
            'door'           => '1a',
            'postal_code'    => '17190',
            'city'           => 'Girona',
            'province'       => 'Girona',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Profile successfully updated.'
        ]);

        $this->assertDatabaseHas('customers', [
            'id'             => $user->customer_id,
            'first_name'     => 'Carles Modified',
            'last_name'      => 'Saubí Modified',
            'phone'          => '972230680',
            'street'         => 'Carrer Nou',
            'address_number' => '20',
            'address_floor'  => '1r',
            'door'           => '1a',
            'postal_code'    => '17190',
        ]);
    }
}
