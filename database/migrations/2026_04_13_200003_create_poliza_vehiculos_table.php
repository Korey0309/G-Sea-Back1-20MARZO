<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poliza_vehiculos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('poliza_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->year('anio')->nullable();
            $table->string('serie')->nullable();
            $table->string('placas')->nullable();

            $table->string('motor')->nullable();
            $table->integer('pasajeros')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliza_vehiculos');
    }
};
