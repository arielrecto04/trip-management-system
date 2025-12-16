<?php

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Driver::class);
            $table->foreignIdFor(Vehicle::class);
            $table->foreignIdFor(Warehouse::class);
            $table->foreignIdFor(User::class, 'dispatcher_id');
            $table->string('status')->index();
            $table->string('customer_name');
            $table->string('customer_address');
            $table->dateTime('planned_start_time')->nullable()->index();
            $table->dateTime('actual_start_time')->nullable(); // for the driver
            $table->json('route'); //json
            $table->decimal('route_distance_km', 8, 2);
            $table->boolean('is_liquidation_required')->default(false)->index();
            $table->boolean('is_pre_trip_checked')->default(false);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
