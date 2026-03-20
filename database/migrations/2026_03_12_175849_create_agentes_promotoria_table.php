<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
                public function up(): void
                    {
                        Schema::create('agentes_promotoria', function (Blueprint $table) {

                        $table->id();

                        $table->foreignId('workspace_id')
                            ->constrained()
                            ->cascadeOnDelete();

                        $table->foreignId('user_id')
                            ->constrained()
                            ->cascadeOnDelete();

                        $table->foreignId('aseguradora_id')
                        ->constrained();

                        $table->string('clave_agente');

                        $table->timestamps();

                            });
                    }

                        public function down(): void
                        {
                            Schema::dropIfExists('agentes_promotoria');
                        }
    
};
