<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('statut_employe', ['actif', 'inactif'])->default('actif');
            $table->string('poste')->nullable();
            $table->timestamp('embauche_le')->nullable();
            $table->timestamp('depart_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['statut_employe', 'poste', 'embauche_le', 'depart_le']);
        });
    }
};