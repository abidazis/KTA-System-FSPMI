<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_number_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('prefix', 20)->default('');
            $table->string('separator', 10)->default('-');
            $table->boolean('include_company_code')->default(true);
            $table->unsignedTinyInteger('sequence_digits')->default(4);
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_number_formulas');
    }
};
