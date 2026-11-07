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
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('affiliate_user_id')->unsigned();
            $table->foreign('affiliate_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->decimal('amount', 22, 4);

            //Bank reference for the payment, entered when it is recorded.
            $table->string('transaction_id')->nullable();

            /*
             * A payout is only ever recorded as paid: there is no approval step
             * before it and no reversal after it.
             */
            $table->enum('status', ['paid'])->default('paid');

            $table->dateTime('paid_at')->nullable();

            //Superadmin who created the payout.
            $table->integer('created_by')->unsigned()->nullable();

            $table->timestamps();

            $table->index(['affiliate_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
