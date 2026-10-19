<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('opticien_id')->constrained('users')->onDelete('cascade');

            // Contenu de l'ordonnance (prescription structurée)
            $table->string('od_sphere')->nullable();
            $table->string('od_cylindre')->nullable();
            $table->string('od_axe')->nullable();
            $table->string('od_addition')->nullable();
            $table->string('og_sphere')->nullable();
            $table->string('og_cylindre')->nullable();
            $table->string('og_axe')->nullable();
            $table->string('og_addition')->nullable();
            $table->string('ecart_pupillaire')->nullable();
            $table->text('recommandations')->nullable();

            // Métadonnées
            $table->string('numero')->unique(); // Numéro unique de l'ordonnance
            $table->date('date_emission');
            $table->date('date_validite')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'cabinet_id']);
            $table->index('date_emission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnances');
    }
};