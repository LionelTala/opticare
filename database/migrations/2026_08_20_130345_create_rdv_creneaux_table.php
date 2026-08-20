<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rdv_creneaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('motif')->nullable();
            $table->enum('statut', [
                'libre',
                'reserve',
                'confirme',
                'annule',
                'termine',
                'non_honore'
            ])->default('libre');
            $table->enum('source', [
                'en_ligne',
                'interne',
                'visiteur'
            ])->default('en_ligne');
            $table->string('visiteur_nom')->nullable();
            $table->string('visiteur_prenom')->nullable();
            $table->string('visiteur_telephone')->nullable();
            $table->string('visiteur_email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cabinet_id', 'date']);
            $table->index(['cabinet_id', 'statut']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdv_creneaux');
    }
};