<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;

class LogoutCustomerTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_customer_can_logout_and_purges_only_their_own_tokens(): void
    {
        $this->setUpPassportPersonalClient();
        
        $user1 = $this->createTestUser('info@fusteriasaubi.com', false);

        $province = new \App\Models\Province();
        $province->province = 'Tarragona';
        $province->save();

        $city = new \App\Models\City();
        $city->city = 'Reus';
        $city->province_id = $province->id;
        $city->save();

        $customer = new \App\Models\Customer();
        $customer->first_name     = 'Jordi';
        $customer->last_name      = 'Torres';
        $customer->phone          = '900000000';
        $customer->street         = 'Carrer Fals';
        $customer->address_number = '123';
        $customer->address_floor  = '1';
        $customer->city_id        = $city->id;
        $customer->postal_code    = '17000';
        $customer->save();

        $user2 = new User();
        $user2->name        = 'testuser';
        $user2->email       = 'test@test.com';
        $user2->password    = \Illuminate\Support\Facades\Hash::make('password_de_prova');
        $user2->customer_id = $customer->id;
        $user2->is_admin    = false;
        $user2->save();

        Passport::actingAs($user1);

        $user1->createToken('API_SERRA_Token'); 
        DB::table('oauth_access_tokens')->insert([
            'id'        => 'token_vell_revocat_usuari_1',
            'user_id'   => $user1->id,
            'client_id' => DB::table('oauth_clients')->first()->id,
            'revoked'   => true,
        ]);
        $user2->createToken('API_SERRA_Token');

        $this->assertEquals(2, DB::table('oauth_access_tokens')->where('user_id', $user1->id)->count());
        $this->assertEquals(1, DB::table('oauth_access_tokens')->where('user_id', $user2->id)->count());

        $response = $this->json('DELETE', '/api/customers/tokens');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 'success',
                     'message' => 'Session successfully closed.'
                 ]);

        $this->assertEquals(0, DB::table('oauth_access_tokens')->where('user_id', $user1->id)->count());

        $this->assertEquals(1, DB::table('oauth_access_tokens')->where('user_id', $user2->id)->count());
    }
}