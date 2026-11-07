<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WhatsAppAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_gateway', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sources');
            $table->integer('is_default');
            $table->string('wa_server');
            $table->string('app_key');
            $table->string('auth_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
