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
        Schema::create('cabinet_optiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('adresse');
            $table->string('ville');
            $table->string('telephone', 20);
            $table->string('email');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('proprietaire_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

             $table->index('is_verified');
            $table->index('proprietaire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabinet_optiques');
    }
};
