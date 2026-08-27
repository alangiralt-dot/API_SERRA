<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCustomerTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    
    public function test_customer_can_register_and_receives_token(): void
    {
        $this->setUpPassportPersonalClient();
        
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