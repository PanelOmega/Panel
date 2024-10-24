<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hosting_subscription_indices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('directory')->nullable();
            $table->string('directory_real_path')->nullable();
            $table->string('directory_type')->nullable();
            $table->string('index_type')->nullable()->default('Inherit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_subscription_indices');
    }
};
