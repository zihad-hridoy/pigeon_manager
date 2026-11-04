<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigeons', function (Blueprint $table) {
            $table->id();
            $table->string('ring_number')->unique();
            $table->string('nickname')->nullable();
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->enum('breed_type', ['pure', 'cross'])->default('pure');
            $table->string('strain_breed')->nullable();
            $table->string('color')->nullable();
            $table->date('hatch_date')->nullable();
            $table->string('status', 50)->default('active');

            $table->foreignId('father_id')->nullable()->constrained('pigeons')->nullOnDelete();
            $table->foreignId('mother_id')->nullable()->constrained('pigeons')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->string('profile_pic_path')->nullable();
            $table->string('eye_pic_path')->nullable();
            $table->string('wing_pic_path')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('father_id');
            $table->index('mother_id');
            $table->index(['owner_id', 'status']);
            $table->index(['gender', 'breed_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigeons');
    }
};
