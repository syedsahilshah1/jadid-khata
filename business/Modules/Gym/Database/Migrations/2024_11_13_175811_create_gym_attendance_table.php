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
        Schema::create('gym_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id');  // Foreign key for contact table
            $table->date('date');           // Date of attendance
            $table->time('in_time')->nullable(); // Check-in time
            $table->time('out_time')->nullable();
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
        Schema::dropIfExists('gym_attendance');
    }
};
