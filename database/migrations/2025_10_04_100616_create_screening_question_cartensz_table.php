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
        Schema::create('screening_question_cartensz', function (Blueprint $table) {
            $table->id();
            $table->string('section')->nullable();
            $table->text('question_text');
            $table->enum('answer_type', ['text', 'number', 'date', 'textarea', 'select', 'checkbox', 'checkbox_textarea']);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_question_cartensz');
    }
};
