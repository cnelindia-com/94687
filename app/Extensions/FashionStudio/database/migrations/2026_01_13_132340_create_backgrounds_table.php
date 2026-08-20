<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('background_name')->nullable();
            $table->string('background_type')->nullable();
            $table->string('background_category')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('exist_type')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_openai', function (Blueprint $table) {
            $table->dropIndex('idx_fashion_studio_products');
            $table->dropColumn(['product_type', 'product_category']);
        });
    }
};
