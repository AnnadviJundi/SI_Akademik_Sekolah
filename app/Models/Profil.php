<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    protected $table = 'profil';

    protected $fillable = ['nama_sekolah', 'alamat', 'telepon', 'email', 'logo_path', 'created_by', 'updated_by'];

    protected static function booted(): void
    {
        static::creating(function (self $profil): void {
            $profil->created_by ??= auth()->id();
            $profil->updated_by ??= auth()->id();
        });

        static::updating(function (self $profil): void {
            $profil->updated_by = auth()->id();
        });
    }
}
