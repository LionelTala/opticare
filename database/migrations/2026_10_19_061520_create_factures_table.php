<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('emise_par')->constrained('users')->onDelete('cascade');

            // Informations facture
            $table->string('numero')->unique(); // Numéro unique de facture
            $table->enum('type', ['acompte', 'solde', 'unique'])->default('unique');
            $table->decimal('montant', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->enum('statut', ['impaye', 'partiel', 'paye', 'annule'])->default('impaye');

            // Dates
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->timestamp('date_paiement')->nullable();

            // Détails
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'cabinet_id']);
            $table->index('statut');
            $table->index('date_emission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};