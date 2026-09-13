<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number', 100)->unique(); // UNIQUE constraint - critical for integrity
            $table->enum('status', ['available', 'used'])->default('available');
            $table->foreignId('member_id')->nullable()->unique()->constrained('members')->onDelete('restrict'); // USED nomor tidak boleh dihapus
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('formula_id')->nullable()->constrained('member_number_formulas')->onDelete('set null');
            $table->string('prefix_value', 50)->default(''); // Company kode atau prefix value yang dipakai
            $table->unsignedInteger('sequence')->default(0); // Sequence number yang dipakai
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['status']);
            $table->index(['company_id', 'status']);
            $table->index(['formula_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_numbers');
    }
};
