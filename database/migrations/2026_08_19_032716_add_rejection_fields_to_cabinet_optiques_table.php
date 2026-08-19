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
        Schema::table('cabinet_optiques', function (Blueprint $table) {
            $table->enum('status', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            $table->text('motif_refus')->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->onDelete('set null');
        });
    
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabinet_optiques', function (Blueprint $table) {
            //
        });
    }
};
