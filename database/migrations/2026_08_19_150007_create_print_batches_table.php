<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 20)->unique();
            $table->foreignId('management_period_id')->constrained()->onDelete('restrict');
            $table->foreignId('printed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('tanggal_cetak');
            $table->integer('jumlah');
            $table->enum('type', ['front', 'back']);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_batches');
    }
};
