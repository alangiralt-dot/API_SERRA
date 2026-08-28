<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $province = Province::create([
            'province' => 'Girona',
        ]);

        $city = City::create([
            'city' => 'Salt',
            'province_id' => $province->id,
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

        User::create([
            'name'        => 'carlessaubi',
            'email'       => 'info@fusteriasaubi.com',
            'password'    => Hash::make('saubi17190'),
            'customer_id' => $customer->id,
            'is_admin'   => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
        if (DB::table('oauth_clients')->count() === 0) {
            Artisan::call('passport:keys', ['--force' => true]);

            Artisan::call('passport:client', [
                '--personal' => true,
                '--name'     => 'API_SERRA Personal Access Client',
                '--provider' => 'users',
            ]);
        }
    }
}