<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\RegPeriksa;
use Illuminate\Http\Request;

class LaporanBillingController extends Controller
{
    public function Billing(Request $request)
    {
        $no_rawat = $request->input('no_rawat');

        // Query pasien berdasarkan no_rawat
        $pasien = $this->getPasienData($no_rawat);

        // Cek apakah data pasien ditemukan
        if (!$pasien) {
            return $this->responseNotFound();
        }

        // Dokter
        $dokter_dpjp = $this->getDokterDpjp($pasien);
        $dokter_igd = $this->getDokterIgd($pasien);

        // Kamar
        $kamar_data = $this->getKamarData($pasien);
        $kamarTerakhir = $this->getKamarTerakhir($kamar_data);
        $bangsalKamar = $this->formatBangsalKamar($kamarTerakhir);
        $totalLamaMenginap = $this->calculateLamaMenginap($kamar_data);
        $hasilPeriode = $this->formatPeriode($kamar_data, $totalLamaMenginap);
        // Biaya Kamar
        $hargaKamar = $this->getHargaKamar($pasien);
        $totalBiayaKamar = $this->calculateTotalBiaya($hargaKamar, 'total');

        $konsultasi = 'KS';
        // Konsultasi Dokter Ralan
        $konsultasiDokterRalan = $this->getDokterRalan($pasien, $konsultasi);
        $totalBiayaKonsultasiDokterRalan = $this->getTotalBiaya($konsultasiDokterRalan);
        // Konsultasi Dokter Ranap
        $konsultasiDokterRanap = $this->getDokterRanap($pasien, $konsultasi);
        $totalBiayaKonsultasiDokterRanap = $this->getTotalBiaya($konsultasiDokterRanap);
        // Konsultasi Dokter & Perawat Ranap
        $konsultasiDokterPerawatRanap = $this->getDokterPerawatRanap($pasien, $konsultasi);
        $totalBiayaKonsultasikonsultasiDokterPerawatRanap = $this->getTotalBiaya($konsultasiDokterPerawatRanap);
        //total biaya konsultasi
        $totalBiayaKonsultasi = $totalBiayaKonsultasiDokterRalan + $totalBiayaKonsultasiDokterRanap +  $totalBiayaKonsultasikonsultasiDokterPerawatRanap;

        $visite = 'KP042';
        // Visite Dokter
        $kunjunganDokter = $this->getDokterRanap($pasien, $visite);
        $totalBiayaVisiteDokter = $this->getTotalBiaya($kunjunganDokter);
        $kunjunganDokterPerawat = $this->getDokterPerawatRanap($pasien, $visite);
        $totalBiayaVisiteDokterPerawat = $this->getTotalBiaya($kunjunganDokterPerawat);
        $totalBiayaVisteRanap = $totalBiayaVisiteDokter + $totalBiayaVisiteDokterPerawat;

        $visiteDrSpesialisRalan = $this->getDokterRalan($pasien, 'KP1');
        $totalBiayaVisiteDokterSpesialisRalan = $this->getTotalBiaya($visiteDrSpesialisRalan);
        $kunjunganDokterPerawatSpesialisRalan = $this->getDokterPerawatRalan($pasien, 'KP1');
        $totalBiayaVisiteDokterPerawatSpesialisRalan = $this->getTotalBiaya($kunjunganDokterPerawatSpesialisRalan);
        $totalBiayaVisteSpesialisRalan = $totalBiayaVisiteDokterSpesialisRalan + $totalBiayaVisiteDokterPerawatSpesialisRalan;

        $visiteDrUmumRalan = $this->getDokterRalan($pasien, 'KP');
        $totalBiayaVisiteDokterUmumRalan = $this->getTotalBiaya($visiteDrUmumRalan);
        $kunjunganDokterPerawatUmumRalan = $this->getDokterPerawatRalan($pasien, 'KP');
        $totalBiayaVisiteDokterPerawatUmumRalan = $this->getTotalBiaya($kunjunganDokterPerawatUmumRalan);

        $totalBiayaVisiteUmumRalan = $totalBiayaVisiteDokterUmumRalan + $totalBiayaVisiteDokterPerawatUmumRalan;
        $totalBiayaViste = $totalBiayaVisteRanap + $totalBiayaVisteSpesialisRalan + $totalBiayaVisiteUmumRalan;


        $pemeriksaan = 'KP037';
        // Pemeriksaan Dokter Ralan
        $pemeriksaanDokterRalan = $this->getDokterRalan($pasien,  $pemeriksaan);
        $totalpemeriksaanDokterRalan = $this->getTotalBiaya($pemeriksaanDokterRalan);
        $pemeriksaanPerawatRalan = $this->getPerawatRalan($pasien,  $pemeriksaan);
        $totalpemeriksaanPerawatRalan = $this->getTotalBiaya($pemeriksaanPerawatRalan);
        $pemeriksaanDokterPerawatRalan = $this->getDokterPerawatRalan($pasien,  $pemeriksaan);
        $totalpemeriksaanDokterPerawatRalan = $this->getTotalBiaya($pemeriksaanDokterPerawatRalan);
        $totalpemeriksaanRalan = $totalpemeriksaanDokterRalan + $totalpemeriksaanPerawatRalan + $totalpemeriksaanDokterPerawatRalan;
        // Pemeriksaan Dokter Ranap
        $pemeriksaanDokterRanap  = $this->getDokterRanap($pasien,  $pemeriksaan);
        $totalpemeriksaanDokterRanap = $this->getTotalBiaya($pemeriksaanDokterRanap);
        $pemeriksaanPerawatRanap = $this->getPerawatRanap($pasien,  $pemeriksaan);
        $totalpemeriksaanPerawatRanap = $this->getTotalBiaya($pemeriksaanPerawatRanap);
        $pemeriksaanDokterPerawatRanap = $this->getDokterPerawatRanap($pasien,  $pemeriksaan);
        $totalPemeriksaanDokterPerawatRanap = $this->getTotalBiaya($pemeriksaanDokterPerawatRanap);
        $totalBiayaPemeriksaanRanap = $totalpemeriksaanDokterRanap + $totalpemeriksaanPerawatRanap + $totalPemeriksaanDokterPerawatRanap;
        $totalBiayaPemeriksaan = $totalpemeriksaanRalan + $totalBiayaPemeriksaanRanap;

        $tindakan = 'TDK';
        // tindakan Dokter Ralan
        $tindakanDokterRalan = $this->getDokterRalan($pasien,  $tindakan);
        $totalTindakanDokterRalan = $this->getTotalBiaya($tindakanDokterRalan);
        $tindakanPerawatRalan = $this->getPerawatRalan($pasien,  $tindakan);
        $totalTindakanPerawatRalan = $this->getTotalBiaya($tindakanPerawatRalan);
        $tindakanDokterPerawatRalan = $this->getDokterPerawatRalan($pasien,  $tindakan);
        $totalTindakanDokterPerawatRalan = $this->getTotalBiaya($tindakanDokterPerawatRalan);
        $totalTindakanRalan = $totalTindakanDokterRalan + $totalTindakanPerawatRalan + $totalTindakanDokterPerawatRalan;
        // tindakan Dokter Ranap
        $tindakanDokterRanap  = $this->getDokterRanap($pasien,  $tindakan);
        $totalTindakanDokterRanap = $this->getTotalBiaya($tindakanDokterRanap);
        $tindakanPerawatRanap = $this->getPerawatRanap($pasien,  $tindakan);
        $totalTindakanPerawatRanap = $this->getTotalBiaya($tindakanPerawatRanap);
        $tindakanDokterPerawatRanap = $this->getDokterPerawatRanap($pasien,  $tindakan);
        $totalTindakanDokterPerawatRanap = $this->getTotalBiaya($tindakanDokterPerawatRanap);
        $totalBiayaTindakanRanap = $totalTindakanDokterRanap + $totalTindakanPerawatRanap +  $totalTindakanDokterPerawatRanap;
        $totalBiayaTindakan = $totalTindakanRalan + $totalBiayaTindakanRanap;

        $perawatan = '-';
        // perawatan Dokter Ralan
        $perawatanDokterRalan = $this->getDokterRalan($pasien,  $perawatan);
        $totalperawatanDokterRalan = $this->getTotalBiaya($perawatanDokterRalan);
        $perawatanPerawatRalan = $this->getPerawatRalan($pasien,  $perawatan);
        $totalperawatanPerawatRalan = $this->getTotalBiaya($perawatanPerawatRalan);
        $perawatanDokterPerawatRalan = $this->getDokterPerawatRalan($pasien,  $perawatan);
        $totalperawatanDokterPerawatRalan = $this->getTotalBiaya($perawatanDokterPerawatRalan);
        $totalperawatanRalan = $totalperawatanDokterRalan + $totalperawatanPerawatRalan + $totalperawatanDokterPerawatRalan;
        // Pemeriksaan Dokter Ranap
        $perawatanDokterRanap  = $this->getDokterRanap($pasien,  $perawatan);
        $totalperawatanDokterRanap = $this->getTotalBiaya($perawatanDokterRanap);
        $perawatanPerawatRanap = $this->getPerawatRanap($pasien,  $perawatan);
        $totalperawatanPerawatRanap = $this->getTotalBiaya($perawatanPerawatRanap);
        $perawatanDokterPerawatRanap = $this->getDokterPerawatRanap($pasien,  $perawatan);
        $totalperawatanDokterPerawatRanap = $this->getTotalBiaya($perawatanDokterPerawatRanap);
        $totalBiayaperawatanRanap = $totalperawatanDokterRanap + $totalperawatanPerawatRanap + $totalperawatanDokterPerawatRanap;
        $totalBiayaperawatan = $totalperawatanRalan + $totalBiayaperawatanRanap;

        $lab_data = $pasien->periksalab && !$pasien->periksalab->isEmpty()
            ? $pasien->periksalab
            ->groupBy(function ($lab) {
                // Kelompokkan berdasarkan nama pemeriksaan dan status
                return ($lab->kode->nm_perawatan ?? '-') . '|' . ($lab->status ?? '-');
            })
            ->map(function ($group, $key) {
                // Pecah nama pemeriksaan dan status dari key
                [$namaPemeriksaan, $status] = explode('|', $key);

                return [
                    'kode' => $group->first()->kd_jenis_prw ?? '-',
                    'Nama Pemeriksaan' => $namaPemeriksaan,
                    'Jumlah rujukan' => $group->count(),
                    'Biaya' => $group->first()->biaya ?? '-',
                    'Status' => $status,
                ];
            })
            ->values() // Menyusun ulang indeks
            : collect([
                [
                    'kode' => '-',
                    'Nama Pemeriksaan' => '-',
                    'Jumlah rujukan' => 0,
                    'Biaya' => 0,
                    'Status' => '-',
                ],
            ]);

        $totalLab = $lab_data->sum('Biaya');

        $detail = $pasien->detailperiksalab && !$pasien->detailperiksalab->isEmpty()
            ? $pasien->detailperiksalab
            ->groupBy(function ($detaillab) {
                // Kelompokkan berdasarkan `kd_jenis_prw`
                return $detaillab->kd_jenis_prw ?? '-';
            })
            ->map(function ($group, $kd_jenis_prw) {
                $firstItem = $group->first(); // Ambil elemen pertama dalam grup
                return [
                    'kode' => $kd_jenis_prw, // Ambil kode dari kunci grup
                    'nama' => $firstItem->kode->nm_perawatan ?? '-', // Validasi jika `nm_perawatan` ada
                    'Jumlah rujukan' => $group->count(), // Hitung jumlah elemen dalam grup
                    'Biaya' => $group->sum('biaya_item') ?? 0, // Hitung total biaya item dalam grup
                ];
            })
            ->values() // Menyusun ulang indeks
            : collect([
                [
                    'kode' => '-',
                    'nama' => '-',
                    'Jumlah rujukan' => 0,
                    'Biaya' => 0,
                ],
            ]);

        $totalDetailLab = $detail->sum('Biaya');

        $totalSemuaLab = $totalLab + $totalDetailLab ;


        $operasiStatus = $pasien->operasi;

        return response()->json([
            'no_rawat' => $pasien->no_rawat,
            'bed' => $bangsalKamar,
            'lama' =>  $hasilPeriode,
            'no_rkm_medis' => $pasien->no_rkm_medis,
            'nm_pasien' => $pasien->pasien->nm_pasien ?? "tidak tahu",
            'alamat' => $pasien->almt_pj ?? "tidak tahu",
            'dpjp' => $dokter_dpjp,
            'dokterIgd' => $dokter_igd,
            'asalPermintaan' => $pasien->kd_poli,
            'register' => $pasien->biaya_reg,
            'kamar' =>  $hargaKamar,
            'totalHrgKamar' => $totalBiayaKamar,

            'KonsultasiDokterRalan' => $konsultasiDokterRalan,
            'TotalHargaKonsultasiDokterRalan' => $totalBiayaKonsultasiDokterRalan,
            'KonsulatsiDokterRanap' => $konsultasiDokterRanap,
            'TotalHargaKonsulatsiDokterRanap' => $totalBiayaKonsultasiDokterRanap,
            'KonsulatsiDokterPerawat' => $konsultasiDokterPerawatRanap,
            'TotalHargaKonsulatsiDokterPerawat' => $totalBiayaKonsultasikonsultasiDokterPerawatRanap,
            'TotalBiayaKonsultasi' => $totalBiayaKonsultasi,

            'KunjunganDokterRanap' => $kunjunganDokter,
            'TotalBiayaDokterRanap' => $totalBiayaVisiteDokter,
            'KunjunganDokterPerawatRanap' => $kunjunganDokterPerawat,
            'TotalBiayaDokterPerawatRanap' =>  $totalBiayaVisiteDokterPerawat,
            'TotalBiayaVisiteRanap' => $totalBiayaVisteRanap,

            'KunjunganDokterSpesialisRalan' => $visiteDrSpesialisRalan,
            'TotalBiayaDokterSpesialisRalan' => $totalBiayaVisiteDokterSpesialisRalan,
            'KunjunganDokterSpesialisPerawatRalan' => $kunjunganDokterPerawatSpesialisRalan,
            'TotalBiayaDokterPerawatSpesialisRalan' =>  $totalBiayaVisiteDokterPerawatSpesialisRalan,
            'TotalBiayaVisiteSpesialisRalan' => $totalBiayaVisteSpesialisRalan,

            'KunjunganDokterUmumRalan' => $visiteDrUmumRalan,
            'TotalBiayaDokterUmumRalan' => $totalBiayaVisiteDokterUmumRalan,
            'KunjunganDokterPerawatUmumRalan' => $kunjunganDokterPerawatUmumRalan,
            'totalBiayaVisiteDokterPerawatUmumRalan' => $totalBiayaVisiteDokterPerawatUmumRalan,
            'TotalBiayaVisiteUmumRalan' => $totalBiayaVisiteUmumRalan,
            'TotalBiayaVisiteUmum' => $totalBiayaViste,

            //pemeriksaan
            'PemeriksaanDokterRalan' => $pemeriksaanDokterRalan,
            'totalPemeriksaanDokterRalan' => $totalpemeriksaanDokterRalan,
            'PemeriksaanPerawatRalan' => $pemeriksaanPerawatRalan,
            'totalPemeriksaanPerawatRalan' => $totalpemeriksaanPerawatRalan,
            'PemeriksaanDokterPerawatRalan' => $pemeriksaanDokterPerawatRalan,
            'totalPemeriksaanDokterPerawatRalan' => $totalpemeriksaanDokterPerawatRalan,
            'totalPemeriksaanRalan' => $totalpemeriksaanRalan,
            // 
            'PemeriksaanDokterRanap' => $pemeriksaanDokterRanap,
            'totalPemeriksaanDokterRanap' => $totalpemeriksaanDokterRanap,
            'PemeriksaanPerawatRanap' => $pemeriksaanPerawatRanap,
            'totalPemeriksaanPerawatRanap' => $totalpemeriksaanPerawatRanap,
            'PemeriksaanDokterPerawatRanap' => $pemeriksaanDokterPerawatRanap,
            'totalPemeriksaanDokterPerawatRanap' => $totalPemeriksaanDokterPerawatRanap,
            'totalPemeriksaanRanap' => $totalBiayaPemeriksaanRanap,
            'totalPemeriksaan' => $totalBiayaPemeriksaan,

            // tindakan
            'tindakanDokterRalan' => $tindakanDokterRalan,
            'totaltindakanDokterRalan' => $totalTindakanDokterRalan,
            'tindakanPerawatRalan' => $tindakanPerawatRalan,
            'totaltindakanPerawatRalan' => $totalTindakanPerawatRalan,
            'tindakanDokterPerawatRalan' => $tindakanDokterPerawatRalan,
            'totaltindakanDokterPerawatRalan' => $totalTindakanDokterPerawatRalan,
            'totaltindakanRalan' => $totalTindakanRalan,
            //
            'tindakanDokterRanap' => $tindakanDokterRanap,
            'totaltindakanDokterRanap' => $totalTindakanDokterRanap,
            'tindakanPerawatRanap' => $tindakanPerawatRanap,
            'totaltindakanRanap' => $totalTindakanPerawatRanap,
            'tindakanDokterPerawatRanap' => $tindakanDokterPerawatRanap,
            'totaltindakanDokterPerawatRanap' => $totalTindakanDokterPerawatRanap,
            'totaltindakanRanap' => $totalBiayaTindakanRanap,
            'totaltindakan' => $totalBiayaTindakan,

            //Perawatan
            'PerawatanDokterRalan' => $perawatanDokterRalan,
            'totalperawatanDokterRalan' => $totalperawatanDokterRalan,
            'perawatanPerawatRalan' => $perawatanPerawatRalan,
            'totalPerawatanPerawatRalan' => $totalperawatanPerawatRalan,
            'perawatanDokterPerawatRalan' => $perawatanDokterPerawatRalan,
            'totalperawatanDokterPerawatRalan' => $totalperawatanDokterPerawatRalan,
            'totalperawatanRalan' => $totalperawatanRalan,

            'perawatanDokterRanap' => $perawatanDokterRanap,
            'totalperawatanDokterRanap' => $totalperawatanDokterRanap,
            'perawatanPerawatRanap' => $perawatanPerawatRanap,
            'totalPerawatanPerawatRanap' => $totalperawatanPerawatRanap,
            'perawatanDokterPerawatRanap' => $perawatanDokterPerawatRanap,
            'totalperawatanDokterPerawatRanap' => $totalperawatanDokterPerawatRanap,
            'totalperawatanRanap' => $totalBiayaperawatanRanap,
            'totalperawatan' => $totalBiayaperawatan,

            'Lab' => $lab_data,
            'TotalLab' => $totalLab,
            'DetailLab' => $detail,
            'TotalDetailLab' => $totalDetailLab,
            'TotalSemuaLab' =>  $totalSemuaLab,

           'Operasi ' => $operasiStatus

        ]);
    }

    private function getPasienData($no_rawat)
    {
        return RegPeriksa::with([
            'pasien',
            'poliklinik',
            'sep',
            'dpjp.dokter',
            'dokterIgd',
            'operasi.paket',
            'rawatinapdr.dokter',
            'rawatinapdr.kode',
            'rawatInapPr.kode',
            'rawatJlDr.kode',
            'rawatJlDr.dokter',
            'rawatJlDrPr.kode',
            'rawatJlDrPr.dokter',
            'rawatJlPr.kode',
            'kamarinap.kamar.bangsal',
            'periksalab.kode',
            'detailperiksalab.kode',
            'periksaRadiologi',
            'hemodialisa',
            'rawatInapDrpr.JnsPerawatanInap'
        ])->where('no_rawat', $no_rawat)->first();
    }

    private function responseNotFound()
    {
        return response()->json([
            'success' => false,
            'message' => 'Data pasien tidak ditemukan',
        ]);
    }

    private function getDokterDpjp($pasien)
    {
        return $pasien->dpjp->first()->dokter->nm_dokter ?? '-';
    }

    private function getDokterIgd($pasien)
    {
        return $pasien->dokterIgd->nm_dokter ?? '-';
    }

    private function getKamarData($pasien)
    {
        return $pasien->kamarinap->map(function ($kamarInap) {
            return [
                'bed' => $kamarInap->kd_kamar ?? '-',
                'bangsal' => $kamarInap->kamar->bangsal->nm_bangsal ?? '-',
                'lama' => $kamarInap->lama ?? '-',
                'awal' => $kamarInap->tgl_masuk ?? '-',
                'akhir' => $kamarInap->tgl_keluar ?? '-',
            ];
        });
    }

    private function getKamarTerakhir($kamar_data)
    {
        return $kamar_data->sortByDesc('akhir')->first();
    }

    private function formatBangsalKamar($kamarTerakhir)
    {
        return $kamarTerakhir['bed'] . ', ' . $kamarTerakhir['bangsal'];
    }

    private function calculateLamaMenginap($kamar_data)
    {
        return array_sum($kamar_data->pluck('lama')->toArray());
    }

    private function formatPeriode($kamar_data, $totalLamaMenginap)
    {
        $tglMasuk = min(array_filter($kamar_data->pluck('awal')->toArray(), fn($date) => $date !== '-'));
        $tglKeluar = max(array_filter($kamar_data->pluck('akhir')->toArray(), fn($date) => $date !== '-'));

        return date('d F Y', strtotime($tglMasuk)) . ' s.d. ' . date('d F Y', strtotime($tglKeluar)) . " ($totalLamaMenginap Hari)";
    }

    private function getHargaKamar($pasien)
    {
        return $pasien->kamarinap->map(function ($kamarInap) {
            return [
                'bed' => $kamarInap->kd_kamar ?? '-',
                'bangsal' => $kamarInap->kamar->bangsal->nm_bangsal ?? '-',
                'tarif' => $kamarInap->trf_kamar ?? '-',
                'lama' => $kamarInap->lama ?? '-',
                'total' => $kamarInap->ttl_biaya ?? '-',
            ];
        });
    }

    private function calculateTotalBiaya($data, $key)
    {
        return $data->sum($key);
    }

    private function getDokterRalan($pasien, $kategori)
    {
        return $pasien->rawatJlDr
            ->filter(function ($rawatJlDr) use ($kategori) {
                return isset($rawatJlDr->kode) && $rawatJlDr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlDr) {
                return [
                    'kode' => $rawatJlDr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlDr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlDr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlDr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatJlDr->biaya_rawat ?? 0,
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }

    private function getDokterPerawatRalan($pasien, $kategori)
    {
        return $pasien->rawatJlDrpr
            ->filter(function ($rawatJlDrpr) use ($kategori) {
                return isset($rawatJlDrpr->kode) && $rawatJlDrpr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlDrpr) {
                return [
                    'kode' => $rawatJlDrpr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlDrpr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlDrpr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlDrpr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatJlDrpr->biaya_rawat ?? 0,
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }

    private function getDokterRanap($pasien, $kategori)
    {
        return $pasien->rawatinapdr
            ->filter(function ($rawatinapdr) use ($kategori) {
                return isset($rawatinapdr->kode) && $rawatinapdr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatinapdr) {
                return [
                    'kode' => $rawatinapdr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatinapdr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatinapdr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatinapdr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatinapdr->biaya_rawat ?? 0,
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }


    private function getDokterPerawatRanap($pasien, $kategori)
    {

        return $pasien->rawatInapDrpr
            ->filter(function ($rawatInapDrpr) use ($kategori) {
                return isset($rawatInapDrpr->JnsPerawatanInap) && $rawatInapDrpr->JnsPerawatanInap->kd_kategori === $kategori;
            })
            ->map(function ($rawatInapDrpr) {
                return [
                    'kode' => $rawatInapDrpr->JnsPerawatanInap->kd_jenis_prw ?? '-',
                    'nama' => $rawatInapDrpr->JnsPerawatanInap->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatInapDrpr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatInapDrpr->dokter->nm_dokter ?? '-',
                    "biaya" => $rawatInapDrpr->biaya_rawat ?? '-',
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }

    private function getPerawatRanap($pasien, $kategori)
    {

        return $pasien->rawatInapPr
            ->filter(function ($rawatInapPr) use ($kategori) {
                return isset($rawatInapPr->kode) && $rawatInapPr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatInapPr) {
                return [
                    'kode' => $rawatInapPr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatInapPr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatInapPr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatInapPr->dokter->nm_dokter ?? '-',
                    "biaya" => $rawatInapPr->biaya_rawat ?? '-',
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }


    private function getPerawatRalan($pasien, $kategori)
    {
        return $pasien->rawatJlPr
            ->filter(function ($rawatJlPr) use ($kategori) {
                return isset($rawatJlPr->kode) && $rawatJlPr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlPr) {
                return [
                    'kode' => $rawatJlPr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlPr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlPr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlPr->dokter->nm_dokter ?? '-',
                    "biaya" => $rawatJlPr->biaya_rawat ?? '-',
                ];
            })
            ->groupBy(function ($item) {
                return $item['kode'] . '-' . $item['kodeDokter'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'kode' => $first['kode'],
                    'nama' => $first['nama'],
                    'kodeDokter' => $first['kodeDokter'],
                    'dokter' => $first['dokter'],
                    'biaya' => $first['biaya'],
                    'jumlah' => $group->count(),
                    'totalBiaya' => $group->sum('biaya'),
                ];
            })
            ->values();
    }

    private function getTotalBiaya($total)
    {
        return $total->sum('totalBiaya');
    }
}
