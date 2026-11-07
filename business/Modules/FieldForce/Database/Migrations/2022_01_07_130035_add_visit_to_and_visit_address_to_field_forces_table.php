<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddVisitToAndVisitAddressToFieldForcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE field_forces MODIFY COLUMN contact_id INT(11)");

        Schema::table('field_forces', function (Blueprint $table) {
            $table->string('visit_to')->nullable()->after('contact_id');
            $table->text('visit_address')->nullable()->after('visit_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
