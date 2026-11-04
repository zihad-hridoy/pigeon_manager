<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pigeon_id')->constrained('pigeons')->cascadeOnDelete();
            $table->string('vaccine_name');
            $table->date('administered_date');
            $table->date('next_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('pigeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};
