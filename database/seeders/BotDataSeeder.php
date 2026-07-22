<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use App\Models\FatherProduct; // Importem el nou Model

class BotDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // php artisan migrate:fresh && php artisan db:seed --class=BotDataSeeder
        // 1. Generar un array a partir del fitxer .json
        $jsonPath = database_path('data/fustes_en_brut.json');
        
        if (!File::exists($jsonPath)) {
            throw new \RuntimeException("Payload file not found at: {$jsonPath}");
        }

        $jsonContent = File::get($jsonPath);
        $products = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON Parsing Error: " . json_last_error_msg());
        }

        // 2. Processar la informació en brut de cada producte pare i dels seus fills
        foreach ($products as $item) {
            // Pas A: Construcció de l'arbre de categories
            $parentId = null;

            foreach ($item['ruta'] as $categoryName) {
                $category = Category::firstOrCreate(
                    [
                        'category'  => $categoryName,
                        'father_id' => $parentId
                    ]
                );
                $parentId = $category->id;
            }

            // Pas B: escriure alguna cosa en els camps de FatherProduct (Taula 7)
            // Extraiem només el nom del fitxer .webp de la URL absoluta del JSON
            $imageName = basename($item['imatge']);

            $fatherProduct = FatherProduct::firstOrCreate(
                [
                    'name' => $item['producte_pare']
                ],
                [
                    'description' => $item['descripcio'] ?? null,
                    'details' => $item['detalls'] ?? null,
                    'image_path'  => 'products/' . $imageName,
                    'category_id' => $parentId // valor remanent després del loop
                ]
            );

            // Pas C: desem els valors de les caracteristiques del producte pare
            if (!empty($item['caracteristiques']) && is_array($item['caracteristiques'])) {
                
                foreach ($item['caracteristiques'] as $clauAtribut => $valorAtribut) {
                    
                    $attribute = \App\Models\Attribute::firstOrCreate([
                        'attribute' => $clauAtribut
                    ]);

                    // Com que no tenim Model per a 'attribute_father_product', fem servir la façana DB
                    \Illuminate\Support\Facades\DB::table('attribute_father_product')->updateOrInsert(
                        [
                            'attribute_id'      => $attribute->id,
                            'father_product_id' => $fatherProduct->id,
                        ],
                        [
                            'value'             => $valorAtribut ?? null
                        ]
                    );
                }
            }
            // Pas D: Processar els productes fill
            if (!empty($item['productes_fills']) && is_array($item['productes_fills'])) {
                
                foreach ($item['productes_fills'] as $fill) {
                    
                    // 1. disponibilitat
                    $availability = \App\Models\Availability::firstOrCreate([
                        'availability' => $fill['disponibilitat'] ?? 'Consultar'
                    ]);
                    // 2. mesures

                    $mesuresNetes = trim($fill['mesures']);

                    if (preg_match('/Ø([0-9]+)X([0-9]+)/i', $mesuresNetes, $matches)) {
                        $width = (int) $matches[1];
                        $height = -1;
                        $length = (int) $matches[2];
                    } elseif (preg_match('/Ø([0-9]+)/i', $mesuresNetes, $matches)) {
                        $width = (int) $matches[1];
                        $height = -1;
                        $length = (int) ($item['caracteristiques']['Longitud (mm)'] ?? 0);
                    } else {
                        $mides = explode('X', str_replace('MM', '', strtoupper($mesuresNetes)));
                        if (count($mides) === 3) {
                            $width  = (int) $mides[0];
                            $height = (int) $mides[1];
                            $length = (int) $mides[2];
                        } elseif (count($mides) === 2) {
                            $width  = (int) $mides[0];
                            $height = (int) $mides[1];
                            $length = 2500; 
                        } else {
                            $width = 0;
                            $height = 0;
                            $length = 0;
                        }
                    }
                    // 3. preu i unitats
                    $currentUnitPrice = 0.0;
                    $units = '€ / u'; 

                    $preuBrut = trim(str_replace("\xC2\xA0", ' ', $fill['preu'])); // "\xC2\xA0" és &nbsp;

                    if (preg_match('/([0-9.]+,[0-9]+)(.+)/', $preuBrut, $matches)) {
                        $currentUnitPrice = str_replace('.', '', $matches[1]);
                        $currentUnitPrice = (float) str_replace(',', '.', $currentUnitPrice);
                        $units = trim($matches[2]); 
                    }
                    // 3.5. Alimentació automàtica i dinàmica de la nova taula diccionari 11
                    $unitModel = \App\Models\Unit::firstOrCreate([
                        'unit' => $units
                    ]);
                    // 4. inventar el preu de cost
                    $costUnitPrice = $currentUnitPrice / 2.1;
                    // 5. omplir els camps de ChildProduct
                    \App\Models\ChildProduct::firstOrCreate(
                        [
                            'reference' => $fill['referencia']
                        ],
                        [
                            'width'              => $width,
                            'height'             => $height,
                            'length'             => $length,
                            'cost_unit_price'    => $costUnitPrice,
                            'current_unit_price' => $currentUnitPrice,
                            'pack'               => (int) ($fill['pack'] ?? 1),
                            'stock'              => 0,
                            'father_product_id'  => $fatherProduct->id,
                            'availability_id'    => $availability->id,
                            'unit_id'            => $unitModel->id // Transmetem l'ID trobat o creat
                        ]
                    );
                }
            }
        } // foreach ($products as $item) {
        \App\Models\ChildProduct::where('reference', '91690088')->update([
            'width'  => 200,
            'height' => 30,
            'length' => 900
        ]);
        
        \Illuminate\Support\Facades\DB::table('statuses')->insertOrIgnore(['id' => 1, 'status' => 'Confirmada']);
        \Illuminate\Support\Facades\DB::table('statuses')->insertOrIgnore(['id' => 2, 'status' => 'En preparació']);
        \Illuminate\Support\Facades\DB::table('statuses')->insertOrIgnore(['id' => 3, 'status' => 'Lliurada']);
        
        \App\Models\Availability::where('availability', '24/48h')->update(['delay_weight' => 10]);
        \App\Models\Availability::where('availability', '3/5 dies')->update(['delay_weight' => 20]);
        \App\Models\Availability::where('availability', 'Consultar')->update(['delay_weight' => 30]);
                        
        $customer = \App\Models\Customer::where('first_name', 'Carles')->first();

       if ($customer) {
            
            $order1 = \App\Models\Order::create([
                'customer_id'        => $customer->id,
                'status_id'          => 3,
                'date'               => '2026-06-10 10:30:00',
                'order_availability' => '24/48h',
                'total_amount'       => 228.09,
            ]);

            \Illuminate\Support\Facades\DB::table('child_product_order')->insert([
                ['order_id' => $order1->id, 'child_product_id' => 1, 'discount' => 0, 'quantity' => 100, 'sale_unit_price' => 1.1600, 'subtotal' => 116.00],
                ['order_id' => $order1->id, 'child_product_id' => 163, 'discount' => 0, 'quantity' => 5, 'sale_unit_price' => 14.5000, 'subtotal' => 72.50],
            ]);

            $order2 = \App\Models\Order::create([
                'customer_id'        => $customer->id,
                'status_id'          => 2,
                'date'               => '2026-06-28 15:45:00',
                'order_availability' => '3/5 dies',
                'total_amount'       => 483.12,
            ]);

            \Illuminate\Support\Facades\DB::table('child_product_order')->insert([
                ['order_id' => $order2->id, 'child_product_id' => 141, 'discount' => 0, 'quantity' => 3, 'sale_unit_price' => 34.1300, 'subtotal' => 102.39],
                ['order_id' => $order2->id, 'child_product_id' => 152, 'discount' => 0, 'quantity' => 5, 'sale_unit_price' => 23.7500, 'subtotal' => 296.88],
            ]);

            $order3 = \App\Models\Order::create([
                'customer_id'        => $customer->id,
                'status_id'          => 1,
                'date'               => '2026-07-03 11:15:00',
                'order_availability' => 'Consultar',
                'total_amount'       => 589.10,
            ]);

            \Illuminate\Support\Facades\DB::table('child_product_order')->insert([
                ['order_id' => $order3->id, 'child_product_id' => 129, 'discount' => 0, 'quantity' => 2, 'sale_unit_price' => 1750.0000, 'subtotal' => 340.20],
                ['order_id' => $order3->id, 'child_product_id' => 159, 'discount' => 0, 'quantity' => 10, 'sale_unit_price' => 81.4800, 'subtotal' => 146.66],
            ]);
        }

    } // public function run(): void
} // class BotDataSeeder extends Seeder
