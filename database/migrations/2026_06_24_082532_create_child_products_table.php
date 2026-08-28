<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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
            $table->boolean('is_discontinued')->default(false);            
            $table->foreignId('father_product_id')->constrained('father_products'); 
            $table->foreignId('availability_id')->constrained('availabilities'); 
            $table->foreignId('unit_id')->constrained('units'); 
        });

        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE child_products ADD CONSTRAINT child_products_width_positive CHECK (width > 0)');
            DB::statement('ALTER TABLE child_products ADD CONSTRAINT child_products_height_positive_or_cilindric CHECK (height > 0 OR height = -1)');
            DB::statement('ALTER TABLE child_products ADD CONSTRAINT child_products_length_positive CHECK (length > 0)');
            DB::statement('ALTER TABLE child_products ADD CONSTRAINT child_products_current_unit_price_positive CHECK (current_unit_price > 0)');
            DB::statement('ALTER TABLE child_products ADD CONSTRAINT child_products_pack_positive CHECK (pack > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('child_products');
    }
};
