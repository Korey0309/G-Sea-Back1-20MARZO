<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('telefono')->nullable();

            $table->date('fecha_nacimiento')->nullable();

            $table->string('curp')->nullable();
            $table->string('rfc')->nullable();

            $table->string('estado')->nullable();
            $table->string('ciudad')->nullable();
            $table->text('direccion')->nullable();

            $table->date('fecha_alta')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
