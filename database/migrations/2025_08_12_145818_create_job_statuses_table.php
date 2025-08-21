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
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_id')->unique();
            $table->json('initial_data')->nullable()->default(null);
            $table->enum('name_job', ['GeneratingVoiceJob', 'FetchVoicesFromFreetts', 'ProcessDelayedApiRequest', 'SyncVoiceStatusesFromFreetts', "SendNewsMailJob"]);
            $table->enum('status',['queued', 'processing', 'finished', 'failed'])->default('queued');
            $table->json('result')->nullable()->default(null);
            $table->foreignId('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_statuses');
    }
};
