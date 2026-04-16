<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_workspace_aseguradora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_workspace_id')
                ->constrained('agente_workspaces')
                ->cascadeOnDelete();
            $table->foreignId('aseguradora_id')->constrained()->cascadeOnDelete();
            $table->string('clave_agente');
            $table->timestamps();

            $table->unique(['agente_workspace_id', 'aseguradora_id'], 'ag_ws_aseg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_workspace_aseguradora');
    }
};
