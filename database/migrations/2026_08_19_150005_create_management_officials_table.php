<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_officials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_period_id')->constrained()->onDelete('cascade');
            $table->string('jabatan', 100);
            $table->string('nama', 100);
            $table->string('signature_path')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['management_period_id', 'jabatan', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_officials');
    }
};
