<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = ['tahun_ajaran', 'semester', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
