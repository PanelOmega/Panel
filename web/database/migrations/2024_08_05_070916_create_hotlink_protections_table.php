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
        Schema::create('hotlink_protections', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('hosting_subscription_id')->nullable();
            $table->string('url_allow_access')->nullable();
            $table->string('block_extensions')->nullable();
            $table->string('allow_direct_requests')->nullable();
            $table->string('redirect_to')->nullable();
            $table->string('enabled')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotlink_protections');
    }
};
