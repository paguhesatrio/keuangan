<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'nik', 'kd_dokter');
    }

}
