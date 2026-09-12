<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add kode column to companies table (unique identifier for company)
        Schema::table('companies', function (Blueprint $table) {
            $table->string('kode', 20)->nullable()->after('name');
            $table->index('kode');
        });

        // 2. Create member_photos table for storing photo hashes
        Schema::create('member_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('cascade');
            $table->string('photo_path', 255);
            $table->string('photo_hash', 64)->nullable(); // SHA-256 hash
            $table->timestamps();

            // Index for fast duplicate detection
            $table->index('photo_hash');
            // Unique on hash per member (member can have one photo, but hash can exist in multiple members)
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['kode']);
            $table->dropColumn('kode');
        });

        Schema::dropIfExists('member_photos');
    }
};
