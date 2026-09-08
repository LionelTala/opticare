<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('opticien_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rdv_id')->nullable()->constrained('rdv_creneaux')->onDelete('set null');
            $table->datetime('date_consultation');
            
            // Identité du patient à la visite
            $table->string('motif');
            $table->string('comment_connu')->nullable();
            $table->date('ancienne_prescription_date')->nullable();
            $table->string('ancienne_prescription_od')->nullable();
            $table->string('ancienne_prescription_og')->nullable();
            
            // Plaintes (cases à cocher)
            $table->boolean('plainte_vision_flou_loin')->default(false);
            $table->boolean('plainte_vision_flou_pres')->default(false);
            $table->boolean('plainte_vision_double')->default(false);
            $table->boolean('plainte_demangeaisons')->default(false);
            $table->boolean('plainte_larmoiement')->default(false);
            $table->text('plainte_autres')->nullable();
            
            // Écart pupillaire
            $table->string('ecart_pupillaire')->nullable();
            
            // Prescription Oeil Droit (OD)
            $table->string('od_sphere')->nullable();
            $table->string('od_cylindre')->nullable();
            $table->string('od_axe')->nullable();
            $table->string('od_addition')->nullable();
            $table->string('od_acuite_loin')->nullable();
            $table->string('od_acuite_pres')->nullable();
            
            // Prescription Oeil Gauche (OG)
            $table->string('og_sphere')->nullable();
            $table->string('og_cylindre')->nullable();
            $table->string('og_axe')->nullable();
            $table->string('og_addition')->nullable();
            $table->string('og_acuite_loin')->nullable();
            $table->string('og_acuite_pres')->nullable();
            
            // Observations
            $table->text('observations')->nullable();
            
            // Statut et verrouillage
            $table->enum('statut', ['en_cours', 'terminee'])->default('en_cours');
            $table->boolean('verrouillee')->default(false);
            
            $table->timestamps();

            $table->index(['patient_id', 'cabinet_id']);
            $table->index('date_consultation');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};