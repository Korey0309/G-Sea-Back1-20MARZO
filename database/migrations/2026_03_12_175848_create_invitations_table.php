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
    Schema::create('invitations', function (Blueprint $table) {

        $table->id();

        $table->string('email');
        $table->string('token')->unique();

        $table->foreignId('workspace_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('role_id')
            ->constrained();

        $table->boolean('used')->default(false);

        $table->timestamp('expires_at')->nullable();

        $table->timestamps();

    });
}

public function down(): void
{
    Schema::dropIfExists('invitations');
}
    


};
