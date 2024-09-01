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
        Schema::create('hosting_subscription_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('hosting_subscription_id')->nullable();
            $table->string('status_code')->nullable();
            $table->string('type')->nullable();
            $table->string('domain')->nullable();
            $table->string('directory')->nullable();
            $table->string('regular_expression')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('match_www');
            $table->boolean('wildcard')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_subscription_redirects');
    }
};
