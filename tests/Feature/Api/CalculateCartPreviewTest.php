<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateCartPreviewTest extends TestCase
{
    use RefreshDatabase;

    public $mockConsoleOutput = false;

    public function test_anonymous_user_can_successfully_preview_cart_calculations(): void
    {
        $this->setUpCatalog();

        $payload = [
            'items' => [
                [
                    'id'       => 6,
                    'quantity' => 30
                ],
                [
                    'id'       => 132,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/orders/previews', $payload);

        $response->assertStatus(200);

        $response->assertJson([
            'id'                 => null,
            'code'               => '-',
            'status'             => 'En curs',
            'date'               => now()->format('d/m/Y H:i'),
            'order_availability' => '-',
            'taxable_basis'     => 785.12,
            'tax'                => 164.88,
            'total'       => 950,
            'order_lines'        => [
                [
                    'id'              => 6,
                    'name'            => 'Producte Pare Test',
                    'reference'       => '90164000',
                    'width'           => 35,
                    'height'          => -1,
                    'length'          => 2500,
                    'pack'            => 15,
                    'quantity'        => 30,
                    'unit_price'      => 6.51,
                    'unit'            => '€ / tira',
                    'subtotal'        => 195.30
                ],
                [
                    'id'              => 132,
                    'name'            => 'Producte Pare Test',
                    'reference'       => '91687027',
                    'width'           => 160,
                    'height'          => 80,
                    'length'          => 12000,
                    'pack'            => 1,
                    'quantity'        => 2,
                    'unit_price'      => 1920.00,
                    'unit'            => '€ / m3',
                    'subtotal'        => 589.82
                ]
            ]
        ]);
    }
}
