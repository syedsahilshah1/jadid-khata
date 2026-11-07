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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'allow_offline_pos')) {
                $table->boolean('allow_offline_pos')->default(1)->after('allow_login');
            }
            if (!Schema::hasColumn('users', 'license_start_date')) {
                $table->dateTime('license_start_date')->nullable()->after('allow_offline_pos');
            }
            if (!Schema::hasColumn('users', 'license_end_date')) {
                $table->dateTime('license_end_date')->nullable()->after('license_start_date');
            }
            if (!Schema::hasColumn('users', 'offline_license_status')) {
                $table->string('offline_license_status', 20)->default('active')->after('license_end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allow_offline_pos', 'license_start_date', 'license_end_date', 'offline_license_status']);
        });
    }
};
