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
        Schema::create('patients_cartensz', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport_number')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('contact')->nullable();
            $table->string('passport_images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients_cartensz');
    }
};
