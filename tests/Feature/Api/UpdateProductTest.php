<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FatherProduct;
use App\Models\ChildProduct;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_administrator_can_update_father_product_and_forces_all_children_to_discontinued(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $fatherId = 1;
        FatherProduct::where('id', $fatherId)->update(['is_discontinued' => 0]);
        ChildProduct::where('father_product_id', $fatherId)->update(['is_discontinued' => 0]);

        $payload = [
            'name'            => 'SÒCOL CANALITZADOR DE FUSTA',
            'is_discontinued' => 1
        ];

        $response = $this->putJson("/api/products/fathers/{$fatherId}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Parent product updated successfully.'
        ]);

        $this->assertDatabaseHas('father_products', [
            'id'              => $fatherId,
            'name'            => 'SÒCOL CANALITZADOR DE FUSTA',
            'is_discontinued' => 1
        ]);

        $totalChildrenCount = ChildProduct::where('father_product_id', $fatherId)->count();
        $discontinuedChildrenCount = ChildProduct::where('father_product_id', $fatherId)->where('is_discontinued', 1)->count();
        
        $this->assertEquals($totalChildrenCount, $discontinuedChildrenCount);
    }

    public function test_administrator_can_update_child_product_properties_independently(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $child = ChildProduct::first();

        $payload = [
            'reference'          => '90154881A',
            'current_unit_price' => 2.4500
        ];

        $response = $this->putJson("/api/products/children/{$child->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Child product updated successfully.'
        ]);

        $this->assertDatabaseHas('child_products', [
            'id'                 => $child->id,
            'reference'          => '90154881A',
            'current_unit_price' => 2.4500,
            'stock'              => $child->stock // It must be kept intact thanks to the 'sometimes'
        ]);
    }

    public function test_administrator_cannot_activate_child_product_if_father_is_discontinued(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $child = ChildProduct::first();
        $child->update(['is_discontinued' => 1]);
        $child->fatherProduct->update(['is_discontinued' => 1]);

        $payload = [
            'is_discontinued' => 0,
            'current_unit_price' => 3.5000
        ];

        $response = $this->putJson("/api/products/children/{$child->id}", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('child_products', [
            'id'                 => $child->id,
            'current_unit_price' => 3.5000,
            'is_discontinued'    => 1
        ]);

        $this->assertEquals(1, $child->fresh()->fatherProduct->is_discontinued);
    }

    public function test_update_father_product_repairs_inconsistency_by_discontinuing_all_active_children(): void
    {
        $this->setUpPassportPersonalClient();
        $this->setUpCatalog();

        $admin = $this->createTestUser('admin@fusteriasaubi.com', true);
        Passport::actingAs($admin);

        $fatherId = 1;
        FatherProduct::where('id', $fatherId)->update(['is_discontinued' => 0]);
        ChildProduct::where('father_product_id', $fatherId)->update(['is_discontinued' => 0]);

        $payload = [
            'is_discontinued' => 1
        ];

        $response = $this->putJson("/api/products/fathers/{$fatherId}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Parent product updated successfully.'
        ]);

        $this->assertDatabaseHas('father_products', [
            'id'              => $fatherId,
            'is_discontinued' => 1
        ]);

        $totalChildrenCount = ChildProduct::where('father_product_id', $fatherId)->count();
        $discontinuedChildrenCount = ChildProduct::where('father_product_id', $fatherId)->where('is_discontinued', 1)->count();
        
        $this->assertEquals($totalChildrenCount, $discontinuedChildrenCount);
    }
}
