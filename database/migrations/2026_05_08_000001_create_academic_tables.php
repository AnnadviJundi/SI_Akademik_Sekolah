<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kelas')->unique();
            $table->string('nama_kelas');
            $table->string('tingkat');
            $table->string('tahun_ajaran');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->string('nis')->unique();
            $table->string('nama');
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnUpdate();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->string('nip')->nullable()->unique();
            $table->string('nama');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel')->unique();
            $table->string('nama_mapel');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran');
            $table->string('semester');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['tahun_ajaran', 'semester']);
        });

        Schema::create('guru_kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnUpdate();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnUpdate();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnUpdate();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['guru_id', 'kelas_id', 'mata_pelajaran_id', 'semester_id'], 'pengampu_unique');
        });

        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnUpdate();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnUpdate();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnUpdate();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnUpdate();
            $table->string('jenis_nilai');
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['siswa_id', 'semester_id', 'mata_pelajaran_id', 'jenis_nilai'], 'nilai_unique');
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnUpdate();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnUpdate();
            $table->string('jenis_pembayaran');
            $table->decimal('jumlah_tagihan', 12, 2);
            $table->decimal('jumlah_dibayar', 12, 2)->default(0);
            $table->string('status')->default('Belum Bayar');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bukti_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayaran')->cascadeOnUpdate();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnUpdate();
            $table->timestamp('uploaded_at');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('profil', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->text('alamat');
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->timestamp('created_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('profil');
        Schema::dropIfExists('bukti_pembayaran');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('guru_kelas_mata_pelajaran');
        Schema::dropIfExists('semester');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
    }
};
