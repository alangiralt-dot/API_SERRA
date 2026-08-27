<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected function setUpPassportPersonalClient(): void
    {
        \Illuminate\Support\Facades\Artisan::call('passport:client', [
            '--personal' => true,
            '--name'     => 'API_SERRA Personal Access Client',
            '--provider' => 'users',
        ]);
    }

    protected function createTestUser(string $email = 'info@fusteriasaubi.com', bool $isAdmin = false): User
    {
        $province = Province::create(['province' => 'Girona']);

        $city = City::create([
            'city'        => 'Salt',
            'province_id' => $province->id
        ]);

        $customer = Customer::create([
            'first_name'     => 'Carles',
            'last_name'      => 'Saubí',
            'phone'          => '972230680',
            'street'         => 'Carrer Cardenal Vidal i Barraquer',
            'address_number' => '18',
            'address_floor'  => 'Planta Baixa',
            'door'           => null,
            'city_id'        => $city->id,
            'postal_code'    => '17190',
        ]);

        return User::create([
            'name'        => 'carlessaubi',
            'email'       => $email,
            'password'    => Hash::make('saubi17190'),
            'customer_id' => $customer->id,
            'is_admin'    => $isAdmin,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
