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
        Schema::create('screening_answer_cartensz', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')
                    ->constrained('screening_question_cartensz')
                    ->onDelete('cascade');
                $table->foreignId('patient_id')
                    ->constrained('patients_cartensz') 
                    ->onDelete('cascade');
                $table->text('answer_text')->nullable();
                $table->integer('queue')->default(0);
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_answer_cartensz');
    }
};
