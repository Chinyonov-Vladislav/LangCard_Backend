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
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->decimal('cost', 8, 2)->default(0);
            $table->foreignId('currency_id')->references('id')->on('currencies')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('tariff_id')->references('id')->on('tariffs')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
