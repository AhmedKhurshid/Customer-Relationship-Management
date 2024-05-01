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
        Schema::create('time_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adminId');
            $table->foreign('adminId')->references('id')->on('users');
            $table->string('type');
            $table->time('timeIn');
            $table->time('timeOut');
            $table->time('late');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_schedules');
    }
};
