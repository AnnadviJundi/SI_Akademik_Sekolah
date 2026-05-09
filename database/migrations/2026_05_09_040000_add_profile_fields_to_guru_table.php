<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('nama');
            $table->string('no_telp', 30)->nullable()->after('alamat');
            $table->string('foto_path')->nullable()->after('no_telp');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'no_telp', 'foto_path']);
        });
    }
};
