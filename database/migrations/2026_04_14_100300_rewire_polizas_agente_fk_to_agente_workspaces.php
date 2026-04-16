<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['agente_id']);
            $table->foreign('agente_id')
                ->references('id')
                ->on('agente_workspaces')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['agente_id']);
            $table->foreign('agente_id')
                ->references('id')
                ->on('agentes_promotoria');
        });
    }
};
