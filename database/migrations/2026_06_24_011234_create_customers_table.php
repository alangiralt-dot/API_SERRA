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
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // Això genera un BigInt Unsigned per defecte a Laravel
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('street');
            $table->string('address_number');
            $table->string('address_floor')->nullable();
            $table->string('door')->nullable();
            $table->foreignId('city_id')->constrained('cities');
            $table->string('postal_code');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
