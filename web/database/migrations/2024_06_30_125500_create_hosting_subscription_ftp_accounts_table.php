<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHostingSubscriptionFtpAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('hosting_subscription_ftp_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_subscription_id')->nullable();
            $table->string('domain')->nullable();
            $table->string('ftp_username')->nullable();
            $table->string('ftp_username_prefix')->nullable();
            $table->string('ftp_password')->nullable();
            $table->string('ftp_path')->nullable();
            $table->string('ftp_quota')->nullable();
            $table->string('ftp_quota_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hosting_subscription_ftp_accounts');
    }
}
