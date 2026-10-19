<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabinet_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('cabinet_optiques')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->boolean('est_interne')->default(false);
            $table->timestamp('date_inscription')->useCurrent();
            $table->timestamps();

            $table->unique(['cabinet_id', 'patient_id']);
            $table->index(['cabinet_id', 'est_interne']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinet_patient');
    }
};