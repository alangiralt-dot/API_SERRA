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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('customer_id')->constrained('customers'); 
            $table->string('code')->unique();
            $table->foreignId('status_id')->constrained('statuses'); 
            $table->dateTime('date'); 
            $table->string('order_availability');
            $table->decimal('total_amount', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
