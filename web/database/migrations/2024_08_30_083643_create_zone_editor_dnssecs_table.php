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
        Schema::create('zone_editor_dnssecs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('key_tag')->nullable();
            $table->string('key_type')->nullable();
            $table->string('algorithm')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_editor_dnssecs');
    }
};
