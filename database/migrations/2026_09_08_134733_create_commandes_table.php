<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->onDelete('cascade');
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('opticien_id')->constrained('users')->onDelete('cascade');
            
            // Informations de la commande
            $table->string('numero_monture')->nullable();
            $table->string('type_verre')->nullable();
            $table->string('teinte')->nullable();
            $table->text('description_foyers')->nullable();
            $table->string('port')->nullable();
            $table->boolean('antireflet')->default(false);
            
            // Diagnostic final par œil
            $table->string('diag_od_sphere')->nullable();
            $table->string('diag_od_cylindre')->nullable();
            $table->string('diag_od_axe')->nullable();
            $table->string('diag_od_addition')->nullable();
            $table->string('diag_og_sphere')->nullable();
            $table->string('diag_og_cylindre')->nullable();
            $table->string('diag_og_axe')->nullable();
            $table->string('diag_og_addition')->nullable();
            
            // Statut et suivi
            $table->enum('statut', [
                'initie',
                'en_cours',
                'en_verification',
                'termine'
            ])->default('initie');
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['consultation_id', 'cabinet_id']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};