<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigeon_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pigeon_id')->constrained('pigeons')->cascadeOnDelete();
            $table->enum('media_type', ['certificate', 'award', 'general'])->default('general');
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index('pigeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigeon_media');
    }
};
