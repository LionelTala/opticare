<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            // user_id est NULLABLE car un patient peut être interne (sans compte)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('ville');
            $table->date('date_naissance')->nullable();
            $table->string('adresse')->nullable();
            $table->text('notes')->nullable(); // Notes internes sur le patient
            $table->timestamps();

            $table->index('user_id');
            $table->index('telephone');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
