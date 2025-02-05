<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JnsPerawatan extends Model
{
    use HasFactory;
      
    protected $table = 'jns_perawatan';
    protected $primaryKey = 'kd_jenis_prw';
    public $incrementing = false;
    public $timestamps = false;

    public function rawatjldr()
    {
        return $this->belongsTo(RawatJlDr::class, 'kd_jenis_prw ', 'kd_jenis_prw');
    }

}
