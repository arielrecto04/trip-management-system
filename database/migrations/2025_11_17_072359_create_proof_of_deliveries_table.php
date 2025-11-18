<?php

use App\Models\TripStop;
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
        Schema::create('proof_of_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TripStop::class);
            $table->string('recipient_name')->nullable();
            $table->decimal('collected_cod_amount', 10, 2)->default(0);
            // When the POD was completed (signature done, photos taken)
            $table->dateTime('epod_timestamp')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_of_deliveries');
    }
};
