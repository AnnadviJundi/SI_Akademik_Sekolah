<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = ['kode_kelas', 'nama_kelas', 'tingkat', 'tahun_ajaran', 'status'];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function pengampu(): HasMany
    {
        return $this->hasMany(Pengampu::class);
    }
}
