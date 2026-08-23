<?php

namespace Tests\Feature\Api;

use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CustomerTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_generate_access_token_with_valid_credentials(): void
    {
        $province = new Province();
        $province->province = 'Girona';
        $province->save();

        $city = new City();
        $city->city = 'Salt';
        $city->province_id = $province->id;
        $city->save();

        $customer = new Customer();
        $customer->first_name     = 'Jordi';
        $customer->last_name      = 'Torres';
        $customer->phone          = '900000000';
        $customer->street         = 'Carrer Fals';
        $customer->address_number = '123';
        $customer->address_floor  = '1';
        $customer->city_id        = $city->id;
        $customer->postal_code    = '17000';
        $customer->save();

        $user = new User();
        $user->name        = 'testuser';
        $user->email       = 'test@test.com';
        $user->password    = Hash::make('password_de_prova');
        $user->customer_id = $customer->id;
        $user->is_admin    = false;
        $user->save();

        // This generates a record in the oauth_clients table,
        // so that $user->createToken('API_SERRA_Token'); does not fail.
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
            '--no-interaction' => true
        ]);

        $payload = [
            'email'    => 'test@test.com',
            'password' => 'password_de_prova',
        ];

        $response = $this->postJson('/api/customers/tokens', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'access_token',
                         'token_type'
                     ]
                 ])
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'token_type' => 'Bearer'
                     ]
                 ]);
    }
}
