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
        Schema::create('gym_health_trackings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('contact_id');  // Foreign key for contact table
            $table->date('date');  // Date of health measurement
            // Health metrics columns
            $table->decimal('neck', 5, 2)->nullable();
            $table->decimal('left_arm', 5, 2)->nullable();
            $table->decimal('right_arm', 5, 2)->nullable();
            $table->decimal('chest', 5, 2)->nullable();
            $table->decimal('upper_waist', 5, 2)->nullable();
            $table->decimal('lower_waist', 5, 2)->nullable();
            $table->decimal('hips', 5, 2)->nullable();
            $table->decimal('left_thigh', 5, 2)->nullable();
            $table->decimal('right_thigh', 5, 2)->nullable();
            $table->decimal('calf', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('shoulders', 5, 2)->nullable();
            $table->decimal('body_fat_percentage', 5, 2)->nullable();
            $table->decimal('visceral_fat', 5, 2)->nullable();
            $table->decimal('subcutaneous_fat', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->decimal('bmr', 5, 2)->nullable();
            $table->decimal('muscle_mass_percentage', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            // Timestamps
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
        Schema::dropIfExists('gym_health_trackings');
    }
};
