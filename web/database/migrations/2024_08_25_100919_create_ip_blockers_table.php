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
        Schema::create('ip_blockers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('blocked_ip')->nullable();
            $table->string('beginning_ip')->nullable();
            $table->string('ending_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_blockers');
    }
};
