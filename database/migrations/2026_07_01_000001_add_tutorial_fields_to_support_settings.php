<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_settings', function (Blueprint $table) {
            $table->string('tutorial_title')->nullable()->after('email');
            $table->string('tutorial_subtitle')->nullable()->after('tutorial_title');
        });
    }

    public function down(): void
    {
        Schema::table('support_settings', function (Blueprint $table) {
            $table->dropColumn(['tutorial_title', 'tutorial_subtitle']);
        });
    }
};
