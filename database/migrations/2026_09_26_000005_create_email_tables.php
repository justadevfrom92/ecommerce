<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('button_text')->nullable();
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('subscribed')->index(); // subscribed | unsubscribed
            $table->string('source')->nullable(); // signup | homepage | admin
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('body');
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('status')->default('draft')->index(); // draft | sending | sent
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->string('subject');
            $table->string('template_key')->nullable()->index();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id')->nullable()->index();
            // sent | delivered | opened | clicked | failed | bounced | complained
            $table->string('status')->default('sent')->index();
            $table->text('error')->nullable();
            $table->timestamp('status_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inbound_messages', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('email'); // email | contact
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('message_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inbound_message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_message_replies');
        Schema::dropIfExists('inbound_messages');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('email_templates');
    }
};
