<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use App\Models\Category;
use App\Models\FatherProduct;
use App\Models\ChildProduct;
use App\Models\Availability;
use App\Models\Unit;
use App\Models\Status;

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

    protected function setUpCatalog(): void
    {
        $category = \App\Models\Category::create([
            'id' => 1,
            'category' => 'Fusta',
            'father_id' => null
        ]);

        $fatherProduct = \App\Models\FatherProduct::create([
            'id'          => 1, 
            'name'        => 'Producte Pare Test', 
            'image_path'  => 'images/test.png',
            'category_id' => $category->id
        ]);

        $availability1 = \App\Models\Availability::create(['id' => 1, 'availability' => '24/48h', 'delay_weight' => 10]);
        $availability2 = \App\Models\Availability::create(['id' => 2, 'availability' => '3/5 dies', 'delay_weight' => 20]);
        $availability3 = \App\Models\Availability::create(['id' => 3, 'availability' => 'Consultar', 'delay_weight' => 30]);

        \App\Models\Unit::create(['id' => 1, 'unit' => '€ / tira']);
        \App\Models\Unit::create(['id' => 2, 'unit' => '€ / metre']);
        \App\Models\Unit::create(['id' => 3, 'unit' => '€ / m3']);

        $product6 = ChildProduct::create([
            'id'                 => 6, 
            'reference'          => '90164000', 
            'width'              => 35, 
            'height'             => -1,
            'length'             => 2500, 
            'cost_unit_price'    => 3.1000, 
            'current_unit_price' => 6.5100,
            'pack'               => 15,
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 1, 
            'unit_id'            => 1
        ]);
        
        $product132 = ChildProduct::create([
            'id'                 => 132, 
            'reference'          => '91687027', 
            'width'              => 160, 
            'height'             => 80, 
            'length'             => 12000, 
            'cost_unit_price'    => 914.2857, 
            'current_unit_price' => 1920.0000, 
            'pack'               => 1, 
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 2, 
            'unit_id'            => 3
        ]);
        
        $product149 = ChildProduct::create([
            'id'                 => 149, 
            'reference'          => '22499900', 
            'width'              => 95, 
            'height'             => 63, 
            'length'             => 2500, 
            'cost_unit_price'    => 7.6714, 
            'current_unit_price' => 16.1100, 
            'pack'               => 1, 
            'stock'              => 60, 
            'father_product_id'  => 1, 
            'availability_id'    => 3, 
            'unit_id'            => 2
        ]);
    }
}
