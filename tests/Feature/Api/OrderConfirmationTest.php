<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ChildProduct;
use App\Models\User;
use App\Models\Status;


class OrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public $mockConsoleOutput = false;
    
    public function test_customer_can_confirm_order_successfully_and_decrements_stock(): void
    {
        // 1. ARRANGEMENT (Preparació de l'entorn de prova)
        // Creem un usuari client i el seu perfil de Customer obligatori per al controlador
        // ==========================================
        // 1. ARRANGEMENT (Omplim les 5 taules respectant els esquemes de MariaDB)
        // ==========================================
        
        // Taula: categories (NOT NULL i Autoincrement)
        $category = \App\Models\Category::create([
            'id' => 1,
            'category' => 'Fusta',
            'father_id' => null
        ]);

        // Taula: father_products (image_path és NOT NULL)
        $fatherProduct = \App\Models\FatherProduct::create([
            'id'          => 1, 
            'name'        => 'Producte Pare Test', 
            'image_path'  => 'images/test.png', // Requerit per definició de taula
            'category_id' => $category->id
        ]);

        // Taula: availabilities (NOT NULL i Autoincrement)
        $availability1 = \App\Models\Availability::create(['id' => 1, 'availability' => '24/48h', 'delay_weight' => 10]);
        $availability2 = \App\Models\Availability::create(['id' => 2, 'availability' => '3/5 dies', 'delay_weight' => 20]);
        $availability3 = \App\Models\Availability::create(['id' => 3, 'availability' => 'Consultar', 'delay_weight' => 30]);

        // Taula: units (NOT NULL i Autoincrement)
        \App\Models\Unit::create(['id' => 1, 'unit' => '€ / tira']);
        \App\Models\Unit::create(['id' => 2, 'unit' => '€ / metre']);
        \App\Models\Unit::create(['id' => 3, 'unit' => '€ / m3']);

        // Taula: child_products (Complint tots els CHECK i tipus decimal)
        $product6 = ChildProduct::create([
            'id'                 => 6, 
            'reference'          => '90164000', 
            'width'              => 35, 
            'height'             => -1, // Passa el CHECK (height = -1)
            'length'             => 2500, 
            'cost_unit_price'    => 3.1000, 
            'current_unit_price' => 6.5100, // Passa el CHECK (> 0)
            'pack'               => 15, // Passa el CHECK (> 0)
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

        // Taula: statuses (NOT NULL i Autoincrement)
        \App\Models\Status::create(['id' => 1, 'status' => 'Confirmada']);

        // NOU: Recuperem l'usuari real creat pel teu DatabaseSeeder
        $user = \App\Models\User::where('email', 'info@fusteriasaubi.com')->first();

        // NOU: Definim les línies que s'enviaran a la petició POST
        $payload = [
            'order_lines' => [
                ['id' => 6, 'quantity' => 60],
                ['id' => 132, 'quantity' => 4],
                ['id' => 149, 'quantity' => 9]
            ]
        ];
        
        // 2. ACT (Execució de la petició de Postman simulada amb el token de l'usuari)
        $response = $this->actingAs($user, 'api') // Simula el Passport Bearer Token [Role: Customer]
                         ->postJson('/api/orders', $payload);

        // 3. ASSERTIONS (Verificació del resultat correcte)
        // Comprovem la resposta HTTP i l'ID de retorn acordat
        $response->assertStatus(200);
        $response->assertJson([
            'order_id' => 1 // Com que és una DB neta, la comanda serà la ID 1
        ]);

        // Verifiquem que la capçalera de la comanda té el PVP total amb el 21% d'IVA aplicat
        $this->assertDatabaseHas('orders', [
            'id'                 => 1,
            'customer_id'        => 1,
            'code'               => 'SERRA-2026-00001',
            'status_id'          => 1,
            'order_availability' => 'Consultar',
            'total_amount'       => 2338.60 // El càlcul del PVP exacte de la teva web
        ]);

        // Verifiquem que s'han inserit correctament els registres a la taula pivot child_product_order
        // C. Verifiquem que s'han inserit les 3 línies amb els seus preus i subtotals calculats
        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 6, 
            'quantity'         => 60,
            'sale_unit_price'  => 6.5100,
            'subtotal'         => 390.60
        ]);
        
        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 132, 
            'quantity'         => 4,
            'sale_unit_price'  => 1920.0000,
            'subtotal'         => 1179.65 // Càlcul de m3 de la teva app
        ]);
        
        $this->assertDatabaseHas('child_product_order', [
            'order_id'         => 1, 
            'child_product_id' => 149, 
            'quantity'         => 9,
            'sale_unit_price'  => 16.1100,
            'subtotal'         => 362.48 // Càlcul de metres lineals de la teva app
        ]);

        // Verifiquem que el teu INNER JOIN ha restat l'estoc correctament
        $this->assertEquals(0, $product6->fresh()->stock);
        $this->assertEquals(56, $product132->fresh()->stock);
        $this->assertEquals(51, $product149->fresh()->stock);
    }
}
