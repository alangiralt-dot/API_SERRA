<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CatalogueMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_endpoint_responds_successfully_with_flat_json_structure()
    {
        $category = new \App\Models\Category();
        $category->id = 99;
        $category->category = 'Test Fictici';
        $category->father_id = null;
        $category->save();

        $response = $this->getJson('/api/menu');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'category',
                         'father_id'
                     ]
                 ]);
    }
}