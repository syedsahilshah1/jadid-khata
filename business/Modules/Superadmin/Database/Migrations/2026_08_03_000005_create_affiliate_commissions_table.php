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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('affiliate_user_id')->unsigned();
            $table->foreign('affiliate_user_id')->references('id')->on('users')->onDelete('cascade');

            /*
             * Unique: commission is earned on a business's first purchase only,
             * so the rule is enforced by the database rather than by whoever
             * remembers to check first.
             */
            $table->integer('referred_business_id')->unsigned()->unique();
            $table->foreign('referred_business_id')->references('id')->on('business')->onDelete('cascade');

            $table->integer('package_id')->unsigned();

            $table->integer('subscription_id')->unsigned();

            //Gateway reference copied from subscriptions.payment_transaction_id.
            $table->string('transaction_id')->nullable();

            $table->decimal('sale_amount', 22, 4);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 22, 4);

            /*
             * No 'paid': payouts settle a running balance rather than named
             * commission rows, so a commission is never marked paid.
             */
            $table->enum('status', ['pending', 'approved', 'cancelled'])
                ->default('pending');

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
        Schema::dropIfExists('affiliate_commissions');
    }
};
