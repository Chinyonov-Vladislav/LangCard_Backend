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
        Schema::create('audiofiles', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->enum('destination', ['original', 'target']);
            $table->foreignId('voice_id')->references('id')->on('voices')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('card_id')->references('id')->on('cards')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiofiles');
    }
};
