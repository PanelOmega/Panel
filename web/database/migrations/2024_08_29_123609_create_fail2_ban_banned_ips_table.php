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
        Schema::create('fail2_ban_banned_ips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('status')->nullable();
            $table->string('service')->nullable();
            $table->string('ban_count')->nullable();
            $table->string('ban_date')->nullable();
            $table->string('ban_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fail2_ban_banned_ips');
    }
};
