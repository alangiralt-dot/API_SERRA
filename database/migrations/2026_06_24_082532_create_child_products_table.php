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
        Schema::create('child_products', function (Blueprint $table) {
            $table->id(); 
            $table->string('reference')->unique(); 
            $table->integer('width'); 
            $table->integer('height'); 
            $table->integer('length'); 
            $table->decimal('cost_unit_price', 8, 4);
            $table->decimal('current_unit_price', 8, 4);
            $table->integer('pack'); 
            $table->integer('stock'); 
            $table->foreignId('father_product_id')->constrained('father_products'); 
            $table->foreignId('availability_id')->constrained('availabilities'); 
            $table->foreignId('unit_id')->constrained('units'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_products');
    }
};
