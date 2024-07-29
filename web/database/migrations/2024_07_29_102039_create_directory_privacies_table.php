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
        Schema::create('directory_privacies', function (Blueprint $table) {
            $table->id();
            $table->string('user')->nullable();
            $table->string('directory')->nullable();
            $table->string('allowed_username')->nullable();
            $table->string('allowed_password')->nullable();
            $table->string('protected')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_privacies');
    }
};
