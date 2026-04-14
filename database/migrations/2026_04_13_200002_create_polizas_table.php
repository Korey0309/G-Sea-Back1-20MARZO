<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('contratante_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('agente_id')
                ->nullable()
                ->constrained('agentes_promotoria');

            $table->foreignId('aseguradora_id')
                ->constrained();

            $table->foreignId('ramo_id')
                ->constrained();

            $table->foreignId('subramo_id')
                ->constrained();

            $table->string('numero_poliza');

            $table->date('fecha_emision')->nullable();
            $table->date('inicio_vigencia');
            $table->date('fin_vigencia');

            $table->decimal('prima_neta', 12, 2)->nullable();
            $table->decimal('iva', 12, 2)->nullable();
            $table->decimal('prima_total', 12, 2)->nullable();

            $table->string('moneda')->default('MXN');

            $table->timestamps();

            $table->unique(['workspace_id', 'numero_poliza']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};
