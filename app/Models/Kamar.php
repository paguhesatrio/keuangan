<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;
    protected $table = 'kamar';
    protected $primaryKey = 'kd_kamar';
    public $incrementing = false;
    public $timestamps = false;

    public function kamarinap()
    {
        return $this->hasMany(KamarInap::class, 'kd_kamar', 'kd_kamar');
    }

    public function bangsal()
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }
}
