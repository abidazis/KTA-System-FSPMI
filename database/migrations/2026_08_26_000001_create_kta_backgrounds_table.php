<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kta_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('front_image', 255)->nullable();
            $table->string('back_image', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kta_backgrounds');
    }
};
