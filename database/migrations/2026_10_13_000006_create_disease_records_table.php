<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pigeon_id')->constrained('pigeons')->cascadeOnDelete();
            $table->string('disease_name');
            $table->date('diagnosis_date');
            $table->text('treatment_given')->nullable();
            $table->boolean('is_quarantined')->default(false);
            $table->enum('recovery_status', ['under_treatment', 'recovered', 'deceased'])->default('under_treatment');
            $table->date('recovery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('pigeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_records');
    }
};
