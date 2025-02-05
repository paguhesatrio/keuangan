<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bangsal extends Model
{
    use HasFactory;
    protected $table = 'bangsal';
    protected $primaryKey = 'kd_bangsal';
    public $incrementing = false;
    public $timestamps = false;

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'kd_bangsal', 'kd_bangsal');
    }
}
