<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->unsignedBigInteger('parent_id')->nullable()->after('description');
            $table->integer('sort_order')->default(0)->after('parent_id');
            $table->boolean('is_active')->default(true)->after('sort_order');
            
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['slug', 'parent_id', 'sort_order', 'is_active']);
        });
    }
};
