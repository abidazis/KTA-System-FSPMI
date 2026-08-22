<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('nama', 100);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->foreignId('province_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('regency_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama', 50);
            $table->date('berlaku_hingga');
            $table->date('tanggal_pembuatan');
            $table->string('foto_path')->nullable();
            $table->enum('status', ['draft', 'ready', 'generated', 'printed', 'active', 'expired', 'inactive'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('nik');
            $table->index('nama');
            $table->index('status');
            $table->index('berlaku_hingga');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
