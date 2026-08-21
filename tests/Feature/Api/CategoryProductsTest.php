<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\FatherProduct;
use App\Models\Availability;
use App\Models\Unit;
use App\Models\ChildProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_terminal_category_endpoint_responds_successfully_with_products(): void
    {
        $parentCategory = new Category();
        $parentCategory->id = 8;
        $parentCategory->category = 'Fusta Exterior';
        $parentCategory->father_id = null;
        $parentCategory->save();

        $childCategory = new Category();
        $childCategory->id = 11;
        $childCategory->category = 'Pals Rodons';
        $childCategory->father_id = 8;
        $childCategory->save();

        $fatherProduct = new FatherProduct();
        $fatherProduct->id = 1;
        $fatherProduct->name = 'RODÓ DE FUSTA MASSÍS';
        $fatherProduct->image_path = 'images/rodo_de_fusta_massis.webp';
        $fatherProduct->category_id = 11;
        $fatherProduct->save();

        $availability = new Availability();
        $availability->id = 1;
        $availability->availability = '24h';
        $availability->delay_weight = 10;
        $availability->save();

        $unit = new Unit();
        $unit->id = 1;
        $unit->unit = '€ / tira';
        $unit->save();

        $childProduct = new ChildProduct();
        $childProduct->id = 100;
        $childProduct->reference = 'A0034';
        $childProduct->width = 38;
        $childProduct->height = 17;
        $childProduct->length = 1500;
        $childProduct->cost_unit_price = 10.50;
        $childProduct->current_unit_price = 22.00;
        $childProduct->pack = 1;
        $childProduct->stock = 5;
        $childProduct->father_product_id = 1;
        $childProduct->availability_id = 1;
        $childProduct->unit_id = 1; 
        $childProduct->save();

        $response = $this->getJson('/api/categories/11/products');

        $response->assertStatus(200)->assertJsonStructure(
            [
                "category" => ["id", "category", "father_id"],
                "products" => [
                    "RODÓ DE FUSTA MASSÍS" => [
                        "*" => [
                            "id",
                            "reference",
                            "width",
                            "height",
                            "length",
                            "current_unit_price",
                            "pack",
                            "stock",
                            "father_product_id",
                            "availability_id",
                            "unit_id",
                            "father_product" => [
                                "id",
                                "name",
                                "description",
                                "details",
                                "image_path",
                                "category_id"
                            ],
                            "availability" => [
                                "id",
                                "availability",
                                "delay_weight"
                            ],
                            "unit" => [
                                "id",
                                "unit"
                            ]
                        ]
                    ]
                ]
            ]
        );

        $response->assertJsonMissingPath('products.RODÓ DE FUSTA MASSÍS.0.cost_unit_price');
    }
    
    public function test_non_terminal_category_endpoint_fails_validation(): void
    {
        $parentCategory = new Category();
        $parentCategory->id = 8;
        $parentCategory->category = 'Fusta Exterior';
        $parentCategory->father_id = null;
        $parentCategory->save();

        $childCategory = new Category();
        $childCategory->id = 11;
        $childCategory->category = 'Pals Rodons';
        $childCategory->father_id = 8;
        $childCategory->save();

        $response = $this->getJson('/api/categories/8/products');

        $response->assertStatus(422)->assertJson([
            'message' => 'Only terminal categories are allowed to have products.'
        ]);
    }
    
    public function test_non_existent_category_endpoint_fails_validation(): void
    {
        $response = $this->getJson('/api/categories/999/products');

        $response->assertStatus(422)->assertJson([
            'message' => 'The selected id is invalid.'
        ]);
    }

}
