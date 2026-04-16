<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobranza_cuotas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('poliza_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('numero_cuota')->default(1);
            $table->date('fecha_programada');
            $table->decimal('monto', 12, 2);
            $table->string('estatus', 20)->default('pendiente');
            $table->string('telefono_notificacion', 30)->nullable();

            $table->timestamps();

            $table->index(['workspace_id', 'fecha_programada']);
            $table->index(['poliza_id', 'numero_cuota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobranza_cuotas');
    }
};
