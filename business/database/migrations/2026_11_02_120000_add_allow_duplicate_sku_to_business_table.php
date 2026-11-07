<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('business', 'allow_duplicate_sku')) {
            Schema::table('business', function (Blueprint $table) {
                $table->boolean('allow_duplicate_sku')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('business', 'allow_duplicate_sku')) {
            Schema::table('business', function (Blueprint $table) {
                $table->dropColumn('allow_duplicate_sku');
            });
        }
    }
};
