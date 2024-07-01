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
            $table->unsignedBigInteger('hosting_subscription_id');
            $table->string('ftp_username');
            $table->string('ftp_password');
            $table->string('domain');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hosting_subscription_ftp_accounts');
    }
}