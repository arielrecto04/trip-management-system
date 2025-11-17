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
            $table->unique('external_order_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('recipient_phone');
            $table->string('pickup_address');
            $table->string('delivery_address');
            $table->decimal('delivery_latitude');
            $table->decimal('delivery_longitude');
            $table->decimal('weight_kg');
            $table->decimal('volume_m3');
            $table->dateTime('required_time_window_start')->nullable();
            $table->dateTime('required_time_window_end')->nullable();
            $table->decimal('collected_cod_amount')->default(0.00);
            $table->string('status')->index();            
            $table->timestamps();
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
