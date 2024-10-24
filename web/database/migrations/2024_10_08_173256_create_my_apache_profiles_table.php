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
        Schema::create('my_apache_profiles', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->longText('packages')->nullable();
            $table->longText('tags')->nullable();
            $table->text('description')->nullable();
            $table->float('version')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_custom')->default(false);
            $table->longText('config')->nullable();
            $table->text('vendor')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_apache_profiles');
    }
};
