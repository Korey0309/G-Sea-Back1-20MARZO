<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poliza_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('poliza_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('tipo');
            $table->string('ruta');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliza_documentos');
    }
};
