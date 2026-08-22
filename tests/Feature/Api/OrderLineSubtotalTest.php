<?php

namespace Tests\Feature\Api;

use App\Models\Availability;
use App\Models\Category;
use App\Models\ChildProduct;
use App\Models\FatherProduct;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class OrderLineSubtotalTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('subtotalDataProvider')]
    public function test_calculates_line_subtotal_correctly_based_on_unit(int $unitId, float $expectedSubtotal): void
    {
        $parentCategory = new Category();
        $parentCategory->id = 8;
        $parentCategory->category = 'Fusta Exterior';
        $parentCategory->father_id = null;
        $parentCategory->save();

        $childCategory = new Category();
        $childCategory->id = 11;
        $childCategory->category = 'Bigues';
        $childCategory->father_id = 8;
        $childCategory->save();

        $fatherProduct = new FatherProduct();
        $fatherProduct->id = 1;
        $fatherProduct->name = 'BIGA DE PI';
        $fatherProduct->image_path = 'images/biga_de_pi.webp';
        $fatherProduct->category_id = 11;
        $fatherProduct->save();

        $availability = new Availability();
        $availability->id = 1;
        $availability->availability = '24h';
        $availability->delay_weight = 10;
        $availability->save();

        $unit = new Unit();
        $unit->id = $unitId;
        $unit->unit = 'unitat que correspongui';
        $unit->save();

        $childProduct = new ChildProduct();
        $childProduct->id = 100;
        $childProduct->reference = 'A0034';
        $childProduct->width = 12;
        $childProduct->height = 34;
        $childProduct->length = 1234;
        $childProduct->cost_unit_price = 5.50;
        $childProduct->current_unit_price = 12.34;
        $childProduct->pack = 1;
        $childProduct->stock = 5;
        $childProduct->father_product_id = 1;
        $childProduct->availability_id = 1;
        $childProduct->unit_id = $unitId; 
        $childProduct->save();

        $response = $this->getJson("/api/orders/line-subtotal?id=100&quantity=4");

        $response->assertStatus(200)->assertJson(['subtotal' => $expectedSubtotal]);
    }

    public static function subtotalDataProvider(): array
    {
        return [
            '€ / tira' => ['unitId' => 1, 'expectedSubtotal' => 49.36],
            '€ / metre' => ['unitId' => 2, 'expectedSubtotal' => 60.91],
            '€ / m3' => ['unitId' => 3, 'expectedSubtotal' => 0.02],
            '€ / unitat' => ['unitId' => 4, 'expectedSubtotal' => 49.36],
            '€ / m2' => ['unitId' => 5, 'expectedSubtotal' => 0.73],
        ];
    }
    
    public function test_line_subtotal_fails_validation_if_quantity_is_not_multiple_of_pack(): void
    {
        $parentCategory = new Category();
        $parentCategory->id = 8;
        $parentCategory->category = 'Fusta Exterior';
        $parentCategory->father_id = null;
        $parentCategory->save();

        $childCategory = new Category();
        $childCategory->id = 11;
        $childCategory->category = 'Bigues';
        $childCategory->father_id = 8;
        $childCategory->save();

        $fatherProduct = new FatherProduct();
        $fatherProduct->id = 1;
        $fatherProduct->name = 'BIGA DE PI';
        $fatherProduct->image_path = 'images/biga_de_pi.webp';
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
        $childProduct->id = 200;
        $childProduct->reference = 'A0034-PACK';
        $childProduct->width = 12;
        $childProduct->height = 34;
        $childProduct->length = 1234;
        $childProduct->cost_unit_price = 5.50;
        $childProduct->current_unit_price = 12.34;
        $childProduct->pack = 15;
        $childProduct->stock = 5;
        $childProduct->father_product_id = 1;
        $childProduct->availability_id = 1;
        $childProduct->unit_id = 1; 
        $childProduct->save();

        $response = $this->getJson('/api/orders/line-subtotal?id=200&quantity=4');

        $response->assertStatus(422)->assertJson([
            'message' => 'The quantity must be a multiple of the product pack (15).'
        ]);
    }
}