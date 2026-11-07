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
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->increments('id');

            //Owner of the affiliate account that referred the business.
            $table->integer('affiliate_user_id')->unsigned();
            $table->foreign('affiliate_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('referred_business_id')->unsigned();
            $table->foreign('referred_business_id')->references('id')->on('business')->onDelete('cascade');

            //Denormalised so the referral survives an affiliate code change.
            $table->string('affiliate_code');

            $table->dateTime('registered_at')->nullable();

            //Value the referral cookie carried at the time of registration.
            $table->dateTime('cookie_expiry')->nullable();

            $table->timestamps();

            //A business is only ever attributed to a single affiliate.
            $table->unique('referred_business_id');
            $table->index('affiliate_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
