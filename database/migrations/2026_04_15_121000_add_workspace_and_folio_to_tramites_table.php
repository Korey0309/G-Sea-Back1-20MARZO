<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->after('id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('folio', 100)->nullable()->after('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropColumn(['workspace_id', 'folio']);
        });
    }
};
