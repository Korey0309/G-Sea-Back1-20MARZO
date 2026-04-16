<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('activo');
            $table->timestamps();

            $table->unique(['agente_id', 'workspace_id']);
            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_workspaces');
    }
};
