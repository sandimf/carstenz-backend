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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nik');
            $table->string('email');
            $table->string('name', 100);
            $table->string('address', 100);
            $table->date('date_of_birth');
            $table->string('phone', 15);
            $table->longText('signature')->nullable();
            $table->enum('role', ['doctor'])
                ->default('doctor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
