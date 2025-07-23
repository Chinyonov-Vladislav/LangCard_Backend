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
        Schema::create('user_test_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('score')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('finish_time')->nullable();
            $table->unsignedInteger('number_attempt');
            $table->foreignId('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('test_id')->references('id')->on('tests')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_results');
    }
};
