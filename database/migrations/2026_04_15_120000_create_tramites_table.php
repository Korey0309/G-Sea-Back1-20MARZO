<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();

            $table->string('tipo', 100)->nullable();
            $table->string('tipo_tramite', 100)->nullable();
            $table->string('etapa', 50)->nullable();
            $table->string('nombre_agente', 255)->nullable();
            $table->string('ramo', 100)->nullable();
            $table->string('subramo', 100)->nullable();
            $table->string('poliza_referencia', 100)->nullable();
            $table->date('fecha_alta')->nullable();
            $table->dateTime('fecha_ultima_modificacion')->nullable();
            $table->string('observaciones', 555)->nullable();
            $table->string('aseguradora', 555)->nullable();
            $table->string('dias_fecha_alta', 555)->nullable();
            $table->string('dias_etapa_actual', 555)->nullable();
            $table->string('semaforo', 555)->nullable();
            $table->string('centro_emisor', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramites');
    }
};
