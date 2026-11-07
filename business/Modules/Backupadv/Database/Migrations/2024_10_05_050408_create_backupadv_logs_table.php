<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupadvLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backupadv_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('user_id');
            $table->string('filename')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->float('size_in_mb')->nullable();
            $table->timestamps();

            // يمكنك إضافة مفاتيح خارجية وعلاقات إذا لزم الأمر
            // Example: 
            // $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backupadv_logs');
    }
}
