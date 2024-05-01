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
        Schema::create('user_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leaveTypeId');
            $table->foreign('leaveTypeId')->references('id')->on('leaves_types');
            $table->unsignedBigInteger('userId');
            $table->foreign('userId')->references('id')->on('users');
            $table->integer('sick')->default(0);
            $table->integer('annual')->default(0);
            $table->integer('casual')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_leaves');
    }
};
