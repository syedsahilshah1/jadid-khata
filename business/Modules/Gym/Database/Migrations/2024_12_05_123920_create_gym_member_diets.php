<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gym_member_diets', function (Blueprint $table) {
            $table->id();
            $table->integer('contact_id')->unsigned();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->string('morning')->nullable();
            $table->string('breakfast')->nullable();
            $table->string('before_lunch')->nullable();
            $table->string('lunch')->nullable();
            $table->string('afternoon')->nullable();
            $table->string('evening')->nullable();
            $table->string('dinner')->nullable();
            $table->string('before_sleep')->nullable();
            $table->string('before_workout')->nullable();
            $table->string('after_workout')->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('');
    }
};
