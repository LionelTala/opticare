<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->decimal('prix', 10, 2)->nullable()->after('notes');
            $table->decimal('prix_paye', 10, 2)->nullable()->after('prix');
            $table->timestamp('paye_le')->nullable()->after('prix_paye');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['prix', 'prix_paye', 'paye_le']);
        });
    }
};