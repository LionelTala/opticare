<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cabinet_optiques', function (Blueprint $table) {
            $table->string('slogan')->nullable();
            $table->text('description')->nullable();
            $table->string('quartier')->nullable();
            $table->string('whatsapp_numero')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('photos')->nullable();
            $table->string('site_web')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->boolean('abonnement_premium')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('cabinet_optiques', function (Blueprint $table) {
            $table->dropColumn([
                'slogan',
                'description',
                'quartier',
                'whatsapp_numero',
                'logo_url',
                'photos',
                'site_web',
                'facebook',
                'instagram',
                'tiktok',
                'abonnement_premium'
            ]);
        });
    }
};