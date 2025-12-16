<?php

use App\Models\User;
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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'contact_person')->nullable();
            $table->string('name')->unique();
            $table->string('house_number')->nullable();
            $table->string('street')->nullable();
            $table->string('region_code', 9);
            $table->string('region_name');
            $table->string('province_code', 9)->nullable();
            $table->string('province_name')->nullable();
            $table->string('city_code', 9);
            $table->string('city_name');
            $table->string('barangay_code', 9);
            $table->string('barangay_name');
            $table->string('zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
