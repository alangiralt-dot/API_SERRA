<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowProfileTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_authenticated_user_can_view_their_own_profile_details(): void
    {
        $this->setUpPassportPersonalClient();

        $user = $this->createTestUser('info@fusteriasaubi.com', false);

        Passport::actingAs($user);

        $response = $this->getJson('/api/customers/profiles');

        $response->assertStatus(200);
        $response->assertExactJson([
            'first_name'     => 'Carles',
            'last_name'      => 'Saubí',
            'phone'          => '972230680',
            'street'         => 'Carrer Cardenal Vidal i Barraquer',
            'address_number' => '18',
            'address_floor'  => 'Planta Baixa',
            'door'           => null,
            'postal_code'    => '17190',
            'city'           => 'Salt',
            'province'       => 'Girona',
        ]);
    }
}
