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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            /*
             * Issued by AffiliateUtil when the application is approved, so
             * it is null while an application is pending and stays null if the
             * application is rejected. MySQL allows any number of NULLs in a
             * unique index, so those rows do not collide with each other.
             */
            $table->string('affiliate_code')->nullable()->unique();

            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('pending');

            //Audit of the terms & conditions acceptance made on the application form.
            $table->dateTime('tc_accepted_at')->nullable();

            $table->timestamps();

            //A user can hold only one affiliate account.
            $table->unique('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliates');
    }
};
