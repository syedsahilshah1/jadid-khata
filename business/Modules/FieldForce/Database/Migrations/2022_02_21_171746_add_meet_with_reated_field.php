<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetWithReatedField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('field_forces', function (Blueprint $table) {
            $table->string('meet_with_mobileno2')->nullable()->after('meet_with_mobileno');
            $table->string('meet_with_mobileno3')->nullable()->after('meet_with_mobileno2');

            $table->string('meet_with2')->nullable()->after('meet_with');
            $table->string('meet_with3')->nullable()->after('meet_with2');

            $table->string('meet_with_designation')->nullable()->after('meet_with3');
            $table->string('meet_with_designation2')->nullable()->after('meet_with_designation');
            $table->string('meet_with_designation3')->nullable()->after('meet_with_designation2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('field_forces', function (Blueprint $table) {
        });
    }
}
