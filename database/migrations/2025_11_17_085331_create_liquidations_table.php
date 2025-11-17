<?php

use App\Models\Driver;
use App\Models\Trip;
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
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Driver::class);
            $table->foreignIdFor(Trip::class)->unique();
            $table->foreignIdFor(User::class, 'auditor_id'); // clerk
            $table->decimal('initial_advance');
            $table->decimal('total_approved_expenses');
            $table->decimal('total_collected_cod');
            $table->decimal('net_cash_due_from_driver');
            $table->dateTime('date_audited');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidations');
    }
};
