<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
    public function up(): void
{
    Schema::create('planes', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->decimal('precio_mensual', 10, 2)->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('planes');
}
    
    
};
