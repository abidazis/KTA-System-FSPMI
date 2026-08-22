<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_batch_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->timestamps();

            $table->unique(['print_batch_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_batch_members');
    }
};
