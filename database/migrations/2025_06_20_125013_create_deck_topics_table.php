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
        Schema::create('deck_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->references('id')->on('topics')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('deck_id')->references('id')->on('decks')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['topic_id', 'deck_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_topics');
    }
};
