<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix NULL prefix_value to empty string for existing records
        DB::table('member_number_sequences')
            ->whereNull('prefix_value')
            ->update(['prefix_value' => '']);

        // Change column to NOT NULL with default empty string
        Schema::table('member_number_sequences', function (Blueprint $table) {
            $table->string('prefix_value', 50)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_number_sequences', function (Blueprint $table) {
            $table->string('prefix_value', 50)->nullable()->change();
        });
    }
};
