<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained('member_number_formulas')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->string('prefix_value', 50)->default('');
            $table->timestamps();

            // Unique constraint: one sequence per formula + company combination
            $table->unique(['formula_id', 'company_id'], 'unique_formula_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_number_sequences');
    }
};
