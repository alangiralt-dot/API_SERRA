<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCustomerTest extends TestCase
{
    use RefreshDatabase;

    // Force Laravel to run the DatabaseSeeder automatically
    protected $seed = true;

    /**
    * DISABLE CONSOLE MOCK (Passport structural fix)
    * This allows the seeder's 'passport:keys' command to write the real
    * key files to the test disk without throwing the Mockery exception.
    */
    public $mockConsoleOutput = false;
    
    public function test_customer_can_register_and_receives_token(): void
    {
        $payload = [
            'first_name'     => 'Pere',
            'last_name'      => 'Mas',
            'phone'          => '600112233',
            'street'         => 'Carrer Major',
            'address_number' => '15',
            'address_floor'  => '2',
            'door'           => '1a',
            'postal_code'    => '17001',
            'city_name'      => 'Girona',
            'province_name'  => 'Girona',
            'email'          => 'pere.mas@test.com',
            'password'       => 'mas17001'
        ];

        $response = $this->postJson('/api/customers', $payload);

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