<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FatherProduct;
use App\Models\ChildProduct;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StoreProductTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_administrator_can_create_new_father_product_and_its_children_in_cascade(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $payload = [
            'name'           => 'SÒCOL CANALITZADOR',
            'image_path'     => 'products/socol-canalitzador.webp',
            'category_id'    => 1,
            'description'    => 'Compta amb canals buits.',
            'child_products' => [
                [
                    'reference'          => '90154881',
                    'width'              => 15,
                    'height'             => 70,
                    'length'             => 2200,
                    'cost_unit_price'    => 0.4500,
                    'current_unit_price' => 1.2500,
                    'pack'               => 10,
                    'stock'              => 150,
                    'availability_id'    => 1,
                    'unit_id'            => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success'
        ]);

        $this->assertDatabaseHas('father_products', [
            'name'            => 'SÒCOL CANALITZADOR',
            'is_discontinued' => 1
        ]);

        $this->assertDatabaseHas('child_products', [
            'reference'       => '90154881',
            'is_discontinued' => 1
        ]);
    }

    public function test_administrator_can_append_discontinued_child_variants_to_existing_father_product(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $payload = [
            'father_product_id' => 1,
            'child_products' => [
                [
                    'reference'          => '90154883',
                    'width'              => 15,
                    'height'             => 120,
                    'length'             => 2500,
                    'cost_unit_price'    => 0.7500,
                    'current_unit_price' => 1.9400,
                    'pack'               => 10,
                    'stock'              => 150,
                    'availability_id'    => 1,
                    'unit_id'            => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('father_products', [
            'id'              => 1,
            'name'            => 'Producte Pare Test'
        ]);
        
        $this->assertDatabaseHas('child_products', [
            'reference'       => '90154883',
            'is_discontinued' => 1
        ]);
    }
}
