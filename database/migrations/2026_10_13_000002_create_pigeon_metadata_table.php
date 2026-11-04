<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigeon_metadata', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['strain_breed', 'color', 'status_tag']);
            $table->string('item_value');
            $table->timestamps();

            $table->unique(['type', 'item_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigeon_metadata');
    }
};
