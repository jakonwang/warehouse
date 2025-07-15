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
        Schema::table('inventory_check_details', function (Blueprint $table) {
            $table->string('series_code')->after('inventory_check_record_id');
            $table->index('series_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_check_details', function (Blueprint $table) {
            $table->dropIndex(['series_code']);
            $table->dropColumn('series_code');
        });
    }
};
