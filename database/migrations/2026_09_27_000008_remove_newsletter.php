<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** The newsletter (subscribers + campaigns) was removed from the store. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_id');
        });
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('subscribers');

        DB::table('email_templates')->where('key', 'newsletter_welcome')->delete();
        $ids = DB::table('permissions')->whereIn('slug', ['emails.subscribers', 'emails.campaigns'])->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // Restoring the newsletter means reverting the commit that removed it.
    }
};
