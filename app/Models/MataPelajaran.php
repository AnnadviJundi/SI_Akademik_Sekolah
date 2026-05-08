<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataPelajaran extends Model
{
    use SoftDeletes;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['kode_mapel', 'nama_mapel', 'status'];

    public function pengampu(): HasMany
    {
        return $this->hasMany(Pengampu::class);
    }
}
