<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ChildProduct;
use App\Models\FatherProduct;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DiscontinueProductTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    
    public function test_administrator_can_discontinue_child_product_if_father_is_active(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);

        Passport::actingAs($admin);

        $response = $this->deleteJson('/api/products/children/6');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Product successfully discontinued.'
        ]);

        $this->assertEquals(1, ChildProduct::find(6)->is_discontinued);
        $this->assertEquals(0, FatherProduct::find(1)->is_discontinued);
    }
    
    public function test_can_discontinue_child_product_even_if_its_father_product_is_already_discontinued(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $father = \App\Models\FatherProduct::find(1);
        $father->update(['is_discontinued' => true]);

        $childId = 6;
        \App\Models\ChildProduct::where('id', $childId)->update(['is_discontinued' => false]);

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $response = $this->deleteJson("/api/products/children/{$childId}");

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Product successfully discontinued.'
        ]);

        $this->assertEquals(1, ChildProduct::find($childId)->is_discontinued);
        $this->assertEquals(1, FatherProduct::find(1)->is_discontinued);
    }

    public function test_administrator_can_discontinue_father_product_and_all_its_child_variants(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $response = $this->deleteJson('/api/products/fathers/1');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Father product and all its child variants successfully discontinued.'
        ]);

        $this->assertEquals(1, \App\Models\FatherProduct::find(1)->is_discontinued);

        $this->assertEquals(1, ChildProduct::find(6)->is_discontinued);
        $this->assertEquals(1, ChildProduct::find(132)->is_discontinued);
        $this->assertEquals(1, ChildProduct::find(149)->is_discontinued);
    }
}
