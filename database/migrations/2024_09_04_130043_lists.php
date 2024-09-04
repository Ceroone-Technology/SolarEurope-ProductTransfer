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
        Schema::create('ACCOUNTS', function (Blueprint $table) {
            $table->id();  // Este es el campo "id" autoincremental.
            $table->string('IDOrigen')->nullable();
            $table->string('IDDestino')->nullable();
            $table->string('name')->nullable();  // Campo 'name'.
            $table->timestamps(); // Este agrega los campos created_at y updated_at automáticamente.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ACCOUNTS');
    }
};
