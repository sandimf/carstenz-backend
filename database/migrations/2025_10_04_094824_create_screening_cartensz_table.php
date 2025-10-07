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
        Schema::create('screening_cartensz', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->foreignId('patient_cartensz_id')
              ->constrained('patients_cartensz')
              ->cascadeOnDelete();
        $table->enum('screening_status', ['completed', 'pending', 'cancelled'])->default('pending');
        $table->enum('health_status', ['pending', 'sehat', 'tidak_sehat'])->default('pending');
        $table->enum('health_check_status', ['pending', 'completed'])->default('pending');
        $table->date('screening_date')->nullable();
        $table->integer('queue')->default(1);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_cartensz');
    }
};
