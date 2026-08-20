<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneaux_horaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->enum('jour_semaine', [
                'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'
            ]);
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('duree_rdv')->default(30);
            $table->time('pause_debut')->nullable();
            $table->time('pause_fin')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->unique(['cabinet_id', 'jour_semaine', 'heure_debut']);
            $table->index(['cabinet_id', 'est_actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creneaux_horaires');
    }
};