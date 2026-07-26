<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::create('child_product_order', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained('orders'); 
            $table->foreignId('child_product_id')->constrained('child_products'); 
            $table->integer('discount')->default(0);
            $table->integer('quantity'); 
            $table->decimal('sale_unit_price', 9, 4); 
            $table->decimal('subtotal', 6, 2);
            
            $table->primary(['order_id', 'child_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_product_order');
    }
};
