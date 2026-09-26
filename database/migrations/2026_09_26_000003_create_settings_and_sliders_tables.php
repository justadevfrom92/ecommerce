<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('home_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // featured | new | sale | department | category
            $table->string('source');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedTinyInteger('max_items')->default(12);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sliders');
        Schema::dropIfExists('settings');
    }
};
