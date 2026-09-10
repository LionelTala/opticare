<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rdv_creneaux', function (Blueprint $table) {
            $table->boolean('rappel_envoye')->default(false)->after('statut');
            $table->timestamp('rappel_envoye_le')->nullable()->after('rappel_envoye');
        });
    }

    public function down(): void
    {
        Schema::table('rdv_creneaux', function (Blueprint $table) {
            $table->dropColumn(['rappel_envoye', 'rappel_envoye_le']);
        });
    }
};