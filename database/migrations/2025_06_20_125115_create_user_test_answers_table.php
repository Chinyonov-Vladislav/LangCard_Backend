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
        Schema::create('user_test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_test_result_id')->references('id')->on('user_test_results')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('question_id')->references('id')->on('questions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('answer_id')->nullable()->references('id')->on('question_answers')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('is_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_answers');
    }
};
