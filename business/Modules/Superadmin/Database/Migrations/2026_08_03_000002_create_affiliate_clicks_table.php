<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('affiliate_id')->unsigned();
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');

            $table->timestamps();

            $table->index(['affiliate_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
