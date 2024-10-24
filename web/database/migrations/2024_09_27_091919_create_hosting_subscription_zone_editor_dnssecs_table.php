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
        Schema::create('hosting_subscription_zone_editor_dnssecs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('domain')->nullable();
            $table->string('key')->nullable();
            $table->string('key_length')->nullable();
            $table->string('key_tag')->nullable();
            $table->string('key_type')->nullable();
            $table->string('algorithm')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_subscription_zone_editor_dnssecs');
    }
};
