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
        Schema::create('task_durations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('startTime');
            $table->dateTime('endTime')->nullable();
            $table->unsignedBigInteger('taskId');
            $table->foreign('taskId')->references('id')->on('tasks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_durations');
    }
};
