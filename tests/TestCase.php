<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Province;
use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
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
        $province = Province::firstOrCreate(['province' => 'Girona']);

        $city = City::firstOrCreate([
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
        $category = Category::create([
            'id' => 1,
            'category' => 'Fusta',
            'father_id' => null
        ]);

        $fatherProduct = FatherProduct::create([
            'id'          => 1, 
            'name'        => 'Producte Pare Test', 
            'image_path'  => 'images/test.png',
            'category_id' => $category->id
        ]);

        $availability1 = Availability::create(['id' => 1, 'availability' => '24/48h', 'delay_weight' => 10]);
        $availability2 = Availability::create(['id' => 2, 'availability' => '3/5 dies', 'delay_weight' => 20]);
        $availability3 = Availability::create(['id' => 3, 'availability' => 'Consultar', 'delay_weight' => 30]);

        Unit::create(['id' => 1, 'unit' => '€ / tira']);
        Unit::create(['id' => 2, 'unit' => '€ / metre']);
        Unit::create(['id' => 3, 'unit' => '€ / m3']);

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
    
    protected function setUpOrdersWithoutDetails(User $user1, User $user2, User $user3): void
    {
        Status::create(['id' => 1, 'status' => 'Confirmada']);
        Status::create(['id' => 2, 'status' => 'En preparació']);
        Status::create(['id' => 3, 'status' => 'Lliurada']);

        Order::create([
            'id'                 => 1,
            'customer_id'        => $user1->customer_id,
            'code'               => 'SERRA-2026-00001',
            'status_id'          => 3,
            'date'               => '2026-06-10 10:30:00',
            'order_availability' => '24/48h',
            'total_amount'       => 785.12
        ]);

        Order::create([
            'id'                 => 2,
            'customer_id'        => $user2->customer_id,
            'code'               => 'SERRA-2026-00002',
            'status_id'          => 3,
            'date'               => '2026-06-28 15:45:00',
            'order_availability' => '3/5 dies',
            'total_amount'       => 695.70
        ]);

        Order::create([
            'id'                 => 3,
            'customer_id'        => $user3->customer_id,
            'code'               => 'SERRA-2026-00003',
            'status_id'          => 1,
            'date'               => '2026-07-03 11:15:00',
            'order_availability' => 'Consultar',
            'total_amount'       => 1395.32
        ]);
    }

    protected function setUpOrderDetailsLines(): void
    {
        $order1 = Order::find(1);
        $order1->childProducts()->attach(6, ['quantity' => 30, 'sale_unit_price' => 6.51, 'subtotal' => 195.30]);
        $order1->childProducts()->attach(132, ['quantity' => 2, 'sale_unit_price' => 1920.00, 'subtotal' => 589.82]);

        $order2 = Order::find(2);
        $order2->childProducts()->attach(6, ['quantity' => 45, 'sale_unit_price' => 6.51, 'subtotal' => 292.95]);
        $order2->childProducts()->attach(149, ['quantity' => 10, 'sale_unit_price' => 16.11, 'subtotal' => 402.75]);

        $order3 = Order::find(3);
        $order3->childProducts()->attach(132, ['quantity' => 2, 'sale_unit_price' => 1920.00, 'subtotal' => 589.82]);
        $order3->childProducts()->attach(149, ['quantity' => 20, 'sale_unit_price' => 16.11, 'subtotal' => 805.50]);
    }
}
