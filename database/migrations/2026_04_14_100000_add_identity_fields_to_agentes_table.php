<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->string('cedula')->nullable()->after('id');
            $table->string('foto_url')->nullable()->after('activo');

            $table->unique('cedula');
            $table->index(['apellido', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropIndex(['apellido', 'nombre']);
            $table->dropUnique(['cedula']);
            $table->dropColumn(['cedula', 'foto_url']);
        });
    }
};
