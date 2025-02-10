<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegPeriksa extends Model
{
    use HasFactory;

    protected $table = 'reg_periksa';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    public $timestamps = false;

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function sep()
    {
        return $this->belongsTo(BridgingSep::class, 'no_rawat', 'no_rawat');
    }

    public function dpjp()
    {
        return $this->hasMany(DpjpRanap::class, 'no_rawat', 'no_rawat');
    }

    public function operasi()
    {
        return $this->hasMany(Operasi::class, 'no_rawat', 'no_rawat');
    }

    public function rawatJl()
    {
        return $this->belongsTo(Operasi::class, 'no_rawat', 'no_rawat');
    }

    public function kamarinap()
    {
        return $this->hasMany(KamarInap::class, 'no_rawat', 'no_rawat');
    }

    public function periksaRadiologi()
    {
        return $this->hasMany(PeriksaRadiologi::class, 'no_rawat', 'no_rawat');
    }

    public function periksalab()
    {
        return $this->hasMany(PeriksaLab::class, 'no_rawat', 'no_rawat');
    }

    public function hemodialisa()
    {
        return $this->hasMany(Hemodialisa::class, 'no_rawat', 'no_rawat');
    }
    
    public function endoskopiTelinga()
    {
        return $this->hasMany(HasilEndoskopiTelinga::class, 'no_rawat', 'no_rawat');
    }

    public function endoskopiHidung()
    {
        return $this->hasMany(HasilEndoskopiTelinga::class, 'no_rawat', 'no_rawat');
    }

    public function endoskopiFaring()
    {
        return $this->hasMany(HasilEndoskopiTelinga::class, 'no_rawat', 'no_rawat');
    }

    public function rawatinapdr()
    {
        return $this->hasMany(RawatInapDr::class, 'no_rawat', 'no_rawat');
    }

    public function rawatInapDrpr()
    {
        return $this->hasMany(RawatInapDrpr::class, 'no_rawat', 'no_rawat');
    }

    public function rawatInapPr()
    {
        return $this->hasMany(RawatInapPr::class, 'no_rawat', 'no_rawat');
    }

    public function dokterIgd()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function rawatJlDr()
    {
        return $this->hasMany(rawatJlDr::class, 'no_rawat', 'no_rawat');
    }

    public function rawatJlDrPr()
    {
        return $this->hasMany(RawatJlDrPr::class, 'no_rawat', 'no_rawat');
    }

    public function rawatJlPr()
    {
        return $this->hasMany(RawatJlPr::class, 'no_rawat', 'no_rawat');
    }

    public function detailperiksalab()
    {
        return $this->hasMany(DetailPeriksaLab::class, 'no_rawat', 'no_rawat');
    }

    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }




}
