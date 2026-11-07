<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSenderColumn extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_gateway', function (Blueprint $table) {
            $table->bigInteger('sender')->nullable()->after('wa_server')->comment('Add sender for MPWA')->nullable();
        });
    }
}