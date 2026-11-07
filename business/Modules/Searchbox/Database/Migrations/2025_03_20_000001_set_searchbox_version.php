<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $version = config('searchbox.module_version', '1.0');
        $exists = DB::table('system')->where('key', 'searchbox_version')->exists();
        if (! $exists) {
            DB::table('system')->insert([
                'key' => 'searchbox_version',
                'value' => $version,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system')->where('key', 'searchbox_version')->delete();
    }
};
