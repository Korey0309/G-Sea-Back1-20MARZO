<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->string('frecuencia_cobro', 20)->default('unico')->after('moneda');
            $table->decimal('monto_cuota', 12, 2)->nullable()->after('frecuencia_cobro');
            $table->string('telefono_notificacion', 30)->nullable()->after('monto_cuota');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn(['frecuencia_cobro', 'monto_cuota', 'telefono_notificacion']);
        });
    }
};
