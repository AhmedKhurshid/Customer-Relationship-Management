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
        Schema::create('emp_attends', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('userId');
            $table->foreign('userId')->references('id')->on('users');
            $table->dateTime('checkIn')->nullable();
            $table->dateTime('checkOut')->nullable();
            // $table->dateTime('breakIn')->nullable();
            // $table->dateTime('breakOut')->nullable();
            $table->string('isBreakIn')->nullable();
            $table->string('late')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_attends');
    }
};
