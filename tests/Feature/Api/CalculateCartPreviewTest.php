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
            'order_availability' => '-',
            'base_imposable'     => 785.12,
            'iva'                => 164.88,
            'total_amount'       => 950.00,
            'order_lines'        => [
                [
                    'name'            => 'Producte Pare Test',
                    'reference'       => '90164000',
                    'width'           => 35,
                    'height'          => -1,
                    'length'          => 2500,
                    'quantity'        => 30,
                    'sale_unit_price' => 6.51,
                    'subtotal'        => 195.30
                ],
                [
                    'name'            => 'Producte Pare Test',
                    'reference'       => '91687027',
                    'width'           => 160,
                    'height'          => 80,
                    'length'          => 12000,
                    'quantity'        => 2,
                    'sale_unit_price' => 1920.00,
                    'subtotal'        => 589.82
                ]
            ]
        ]);
    }
}
