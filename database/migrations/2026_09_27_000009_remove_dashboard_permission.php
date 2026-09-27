<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The admin dashboard page was removed; drop its permission. */
return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('permissions')->where('slug', 'dashboard.view')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        //
    }
};
