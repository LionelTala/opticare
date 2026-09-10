<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('consultation_id')->unique()->constrained('consultations')->onDelete('cascade');
            $table->integer('note'); // 1 à 5
            $table->text('commentaire')->nullable();
            $table->boolean('est_publie')->default(true);
            $table->timestamps();

            $table->index(['cabinet_id', 'est_publie']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};