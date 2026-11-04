<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pigeon_id')->constrained('pigeons')->cascadeOnDelete();
            $table->enum('event_type', ['training', 'race', 'endurance_test'])->default('training');
            $table->date('flight_date');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('flight_speed_mpm', 7, 2)->nullable();
            $table->integer('rank_position')->nullable();
            $table->string('weather_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('pigeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_records');
    }
};
