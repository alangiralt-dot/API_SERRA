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
        Schema::create('attribute_father_product', function (Blueprint $table) {
            $table->foreignId('attribute_id')->constrained('attributes'); 
            $table->foreignId('father_product_id')->constrained('father_products'); 
            $table->string('value')->nullable();
            $table->primary(['attribute_id', 'father_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_father_product');
    }
};
