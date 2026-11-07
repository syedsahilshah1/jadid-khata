<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldForcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_forces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('visit_id');
            $table->integer('business_id');
            $table->integer('contact_id');
            $table->integer('assigned_to');
            $table->text('visited_address')->nullable();
            $table->string('status');
            $table->dateTime('visit_on');
            $table->dateTime('visited_on')->nullable();
            $table->text('visit_for')->nullable();
            $table->text('comments')->nullable();
            $table->text('reason_to_not_meet_contact')->nullable();
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
        Schema::dropIfExists('field_forces');
    }
}
