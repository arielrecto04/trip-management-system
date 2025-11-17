<?php

use App\Models\Order;
use App\Models\Trip;
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
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trip::class)->index();
            $table->foreignIdFor(Order::class)->index();
            $table->integer('sequence_no')->index();
            $table->string('stop_name');
            $table->boolean('is_pickup_stop')->default(false);
            $table->boolean('is_delivery_stop')->default(false);
            $table->decimal('planned_latitude');
            $table->decimal('planned_longitude');
            $table->dateTime('planned_arrival_time')->nullable();
            $table->dateTime('actual_arrival_time')->nullable();
            $table->decimal('actual_arrival_latitude')->nullable();
            $table->decimal('actual_arrival_longitude')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('exception_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
