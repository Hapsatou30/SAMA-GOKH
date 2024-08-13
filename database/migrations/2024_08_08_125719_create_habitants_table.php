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
        Schema::create('habitants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('adresse');
            $table->string('sexe');
            $table->date('date_naiss');
            $table->string('photo')->nullable();
            $table->string('profession');
            $table->string('numero_identite')->unique();
            $table->string('image_cni');
            $table->foreignId('municipalite_id')->constrained('municipalites')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitants');
    }
};
