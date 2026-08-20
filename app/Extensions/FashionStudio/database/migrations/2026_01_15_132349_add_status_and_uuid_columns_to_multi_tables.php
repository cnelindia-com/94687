<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wardrobe', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->uuid('generation_uuid')->nullable();
        });

        Schema::table('fashion_model', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->uuid('generation_uuid')->nullable();
        });

        Schema::table('pose', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->uuid('generation_uuid')->nullable();
        });

        Schema::table('background', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->uuid('generation_uuid')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wardrobe', function (Blueprint $table) {
            $table->dropColumn(['status', 'generation_uuid']);
        });

        Schema::table('fashion_model', function (Blueprint $table) {
            $table->dropColumn(['status', 'generation_uuid']);
        });

        Schema::table('pose', function (Blueprint $table) {
            $table->dropColumn(['status', 'generation_uuid']);
        });

        Schema::table('background', function (Blueprint $table) {
            $table->dropColumn(['status', 'generation_uuid']);
        });
    }
};
