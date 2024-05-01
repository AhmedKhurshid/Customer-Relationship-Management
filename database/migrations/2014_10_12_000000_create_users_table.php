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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employeeId')->nullable();
            $table->integer('schedule')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role');
            $table->string('status');
            // $table->integer('otpCode')->nullable();
            $table->text('address')->nullable();
            $table->integer('designation');
            $table->string('cnic');
            $table->string('famContactNo');
            $table->string('contactNo')->nullable();
            $table->string('image')->nullable();
            $table->date('joinDate')->nullable();
            $table->string('imageStatus')->nullable();
            $table->string('isEmployee')->nullable();
            $table->string('deviceToken')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
