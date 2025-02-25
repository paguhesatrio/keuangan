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

        $periksalab = collect($pasien->periksalab ?? []);
        $detailperiksalab = collect($pasien->detailperiksalab ?? []);

        $groupedLab = function ($status, $groupingKey) use ($periksalab) {
            return $periksalab->where('status', $status)
                ->groupBy($groupingKey)
                ->map(function ($group) {
                    return [
                        'kode' => $group->first()->kd_jenis_prw ?? '-',
                        'Nama Pemeriksaan' => optional($group->first()->kode)->nm_perawatan ?? '-',
                        'Jumlah rujukan' => $group->count(),
                        'Biaya' => $group->sum('biaya') ?? 0,
                        'tgl' => $group->first()->tgl_periksa ?? '-',
                        'jam' => $group->first()->jam ?? '-',
                    ];
                })->values();
        };

        // Data lab berdasarkan status (Ralan & Ranap)
        $labData = $periksalab->isNotEmpty()
            ? [
                'Ralan' => $groupedLab('Ralan', fn($item) => $item->kd_jenis_prw),
                'Ranap' => $groupedLab('Ranap', fn($item) => $item->kd_jenis_prw),
                'total_harga_ralan' => $periksalab->where('status', 'Ralan')->sum('biaya'),
                'total_harga_ranap' => $periksalab->where('status', 'Ranap')->sum('biaya'),
            ]
            : [
                'Ralan' => collect([['kode' => '-', 'Nama Pemeriksaan' => '-', 'Jumlah rujukan' => 0, 'Biaya' => 0]]),
                'Ranap' => collect([['kode' => '-', 'Nama Pemeriksaan' => '-', 'Jumlah rujukan' => 0, 'Biaya' => 0]]),
                'total_harga_ralan' => 0,
                'total_harga_ranap' => 0,
            ];

        $totalLabRalan = $labData['total_harga_ralan'];
        $totalLabRanap = $labData['total_harga_ranap'];
        $totalLab = $labData['total_harga_ralan'] + $labData['total_harga_ranap'];

        // Variabel labDummy dengan grouping berdasarkan kode, tanggal, dan jam
        $labDummy = $periksalab->isNotEmpty()
            ? [
                'Ralan' => $groupedLab('Ralan', fn($item) => "{$item->kd_jenis_prw}|{$item->tgl_periksa}|{$item->jam}"),
                'Ranap' => $groupedLab('Ranap', fn($item) => "{$item->kd_jenis_prw}|{$item->tgl_periksa}|{$item->jam}"),
                'total_harga_ralan' => $labData['total_harga_ralan'],
                'total_harga_ranap' => $labData['total_harga_ranap'],
            ]
            : $labData;

        // Detail lab dengan filter berdasarkan $labDummy
        $detailLab = $detailperiksalab->isNotEmpty()
            ? [
                'Ralan' => $detailperiksalab->filter(fn($item) => collect($labDummy['Ralan'])
                    ->where('kode', $item->kd_jenis_prw)
                    ->where('tgl', $item->tgl_periksa)
                    ->where('jam', $item->jam)
                    ->isNotEmpty())
                    ->groupBy(fn($item) => $item->kd_jenis_prw)
                    ->map(function ($group) {
                        return [
                            'kode' => $group->first()->kd_jenis_prw ?? '-',
                            'nama' => optional($group->first()->kode)->nm_perawatan ?? '-',
                            'Jumlah rujukan' => $group->count(),
                            'Biaya' => $group->sum('biaya_item') ?? 0,
                        ];
                    })->values(),

                'Ranap' => $detailperiksalab->filter(fn($item) => collect($labDummy['Ranap'])
                    ->where('kode', $item->kd_jenis_prw)
                    ->where('tgl', $item->tgl_periksa)
                    ->where('jam', $item->jam)
                    ->isNotEmpty())
                    ->groupBy(fn($item) => $item->kd_jenis_prw)
                    ->map(function ($group) {
                        return [
                            'kode' => $group->first()->kd_jenis_prw ?? '-',
                            'nama' => optional($group->first()->kode)->nm_perawatan ?? '-',
                            'Jumlah rujukan' => $group->count(),
                            'Biaya' => $group->sum('biaya_item') ?? 0,
                        ];
                    })->values()
            ]
            : ['Ralan' => collect([]), 'Ranap' => collect([])];

        $totalDetailLabRalan = collect($detailLab['Ralan'])->sum('Biaya');
        $totalDetailLabRanap = collect($detailLab['Ranap'])->sum('Biaya');
        $totalDetailLab = collect($detailLab['Ralan'])->sum('Biaya') + collect($detailLab['Ranap'])->sum('Biaya');
        $totalSemuaLab = $totalLab + $totalDetailLab;

        //Radiologi
        // $radiologi = $pasien->periksaRadiologi;
        $radiologi = $pasien->periksaRadiologi && !$pasien->periksaRadiologi->isEmpty()
            ? [
                'Ralan' => $pasien->periksaRadiologi->where('status', 'Ralan')
                    ->groupBy('kd_jenis_prw')
                    ->map(function ($group) {
                        return [
                            'Nama pemeriksaan' => $group->first()->jnsPerawatan->nm_perawatan ?? '-',
                            'Kode pemeriksaan' => $group->first()->jnsPerawatan->kd_jenis_prw ?? '-',
                            'Biaya Pemeriksaan' => $group->first()->jnsPerawatan->total_byr ?? '-',
                            'Jumlah' => $group->count(),
                            'Total Biaya' => $group->sum('biaya') ?? 0,
                        ];
                    })->values(),
                'total_harga_ralan' => $pasien->periksaRadiologi->where('status', 'Ralan')->sum('biaya'),

                'Ranap' => $pasien->periksaRadiologi->where('status', 'Ranap')
                    ->groupBy('kd_jenis_prw')
                    ->map(function ($group) {
                        return [
                            'Nama pemeriksaan' => $group->first()->jnsPerawatan->nm_perawatan ?? '-',
                            'Kode pemeriksaan' => $group->first()->jnsPerawatan->kd_jenis_prw ?? '-',
                            'Biaya Pemeriksaan' => $group->first()->jnsPerawatan->total_byr ?? '-',
                            'Jumlah' => $group->count(),
                            'Total Biaya' => $group->sum('biaya') ?? 0,
                        ];
                    })->values(),
                'total_harga_ranap' => $pasien->periksaRadiologi->where('status', 'Ranap')->sum('biaya'),

                'total_harga_semua' => $pasien->periksaRadiologi->sum('biaya'),
            ]
            : [
                'Ralan' => collect([
                    [
                        'Nama pemeriksaan' => '-',
                        'Kode pemeriksaan' => '-',
                        'Biaya Pemeriksaan' => 0,
                        'Jumlah' => 0,
                        'Total Biaya' => 0,
                    ],
                ]),
                'Ranap' => collect([
                    [
                        'Nama pemeriksaan' => '-',
                        'Kode pemeriksaan' => '-',
                        'Biaya Pemeriksaan' => 0,
                        'Jumlah' => 0,
                        'Total Biaya' => 0,
                    ],
                ]),
                'total_harga_ralan' => 0,
                'total_harga_ranap' => 0,
                'total_harga_semua' => 0,
            ];


        $totalradiologiRalan = collect($radiologi['Ralan'])->sum('Total Biaya');
        $totalradiologiRanap = collect($radiologi['Ranap'])->sum('Total Biaya');
        $totalradiologi = $totalradiologiRalan + $totalradiologiRanap;

        // Operasi
        $operasiStatus = $pasien->operasi && !$pasien->operasi->isEmpty()
            ? [
                'Ralan' => $pasien->operasi->where('status', 'Ralan')
                    ->map(function ($operasi) {
                        return [
                            'Nama Operasi' => optional($operasi->paket)->nm_perawatan ?? '-',
                            'Kode Operasi' => optional($operasi->paket)->kode_paket ?? '-',
                            'Biaya Operasi' => optional($operasi->paket)->alat ?? 0,
                            'Jumlah' => 1, // Jika ada jumlah, ganti dengan nilai yang benar
                            'Total Biaya' => $operasi->biayaalat ?? 0, // Gunakan biaya dari operasi
                        ];
                    })->values(),
                'total_harga_ralan' => $pasien->operasi->where('status', 'Ralan')->sum('biayaalat'), // Gunakan langsung

                'Ranap' => $pasien->operasi->where('status', 'Ranap')
                    ->map(function ($operasi) {
                        return [
                            'Nama Operasi' => optional($operasi->paket)->nm_perawatan ?? '-',
                            'Kode Operasi' => optional($operasi->paket)->kode_paket ?? '-',
                            'Biaya Operasi' => optional($operasi->paket)->alat ?? 0,
                            'Jumlah' => 1,
                            'Total Biaya' => $operasi->biayaalat ?? 0, // Gunakan biaya dari operasi
                        ];
                    })->values(),
                'total_harga_ranap' => $pasien->operasi->where('status', 'Ranap')->sum('biayaalat'),

                'total_harga_semua' => $pasien->operasi->sum('biayaalat'), // Gunakan langsung
            ]
            : [
                'Ralan' => collect([
                    [
                        'Nama Operasi' => '-',
                        'Kode Operasi' => '-',
                        'Biaya Operasi' => 0,
                        'Jumlah' => 0,
                        'Total Biaya' => 0,
                    ],
                ]),
                'Ranap' => collect([
                    [
                        'Nama Operasi' => '-',
                        'Kode Operasi' => '-',
                        'Biaya Operasi' => 0,
                        'Jumlah' => 0,
                        'Total Biaya' => 0,
                    ],
                ]),
                'total_harga_ralan' => 0,
                'total_harga_ranap' => 0,
                'total_harga_semua' => 0,
            ];

        $totaloperasiStatusRalan = collect($operasiStatus['Ralan'])->sum('Total Biaya');
        $totaloperasiStatusRanap = collect($operasiStatus['Ranap'])->sum('Total Biaya');
        $totaloperasiStatus = $totaloperasiStatusRalan + $totaloperasiStatusRanap;

        //Obat
        $obat = $pasien->obat && !$pasien->obat->isEmpty()
            ? [
                'Ralan' => $pasien->obat->where('status', 'Ralan')
                    ->groupBy('kode_brng')
                    ->map(function ($group) {
                        return [
                            'Nama obat' => $group->first()->barang->nama_brng ?? '-',
                            'kode obat' => $group->first()->barang->kode_brng ?? '-',
                            'Harga Asli' => $group->first()->barang->ralan ?? 0,
                            'Jumlah' => $group->sum('jml') ?? 0,
                            'Harga' => $group->sum('total') ?? 0,
                        ];
                    })->values(),
                'total_harga_ralan' => $pasien->obat->where('status', 'Ralan')->sum('total'),

                'Ranap' => $pasien->obat->where('status', 'Ranap')
                    ->groupBy('kode_brng')
                    ->map(function ($group) {
                        return [
                            'Nama obat' => $group->first()->barang->nama_brng ?? '-',
                            'kode obat' => $group->first()->barang->kode_brng ?? '-',
                            'Harga Asli' => $group->first()->barang->ralan ?? 0,
                            'Jumlah' => $group->sum('jml') ?? 0,
                            'Harga' => $group->sum('total') ?? 0,
                        ];
                    })->values(),
                'total_harga_ranap' => $pasien->obat->where('status', 'Ranap')->sum('total'),

                'total_harga_semua' => $pasien->obat->sum('total'),
            ]
            : [
                'Ralan' => collect([
                    [
                        'Nama obat' => '-',
                        'Harga Asli' => 0,
                        'Jumlah' => 0,
                        'Harga' => 0,
                    ],
                ]),
                'Ranap' => collect([
                    [
                        'Nama obat' => '-',
                        'Harga Asli' => 0,
                        'Jumlah' => 0,
                        'Harga' => 0,
                    ],
                ]),
                'total_harga_ralan' => 0,
                'total_harga_ranap' => 0,
                'total_harga_semua' => 0,
            ];

        $totalobatRalan = collect($obat['Ralan'])->sum('Harga');
        $totalobatRanap = collect($obat['Ranap'])->sum('Harga');
        $totalobat = $totalobatRalan + $totalobatRanap;

        //resep pulang
        $resepPulang = $pasien->resepPulang && !$pasien->resepPulang->isEmpty()
            ? $pasien->resepPulang->groupBy('kode_brng')->map(function ($group) {
                return [
                    'Nama' => $group->first()->barang->nama_brng ?? '-',
                    'harga awal' => $group->first()->harga ?? '-',
                    'Jumlah' => $group->sum('jml_barang') ?? 0,
                    'harga' => $group->sum('total'),
                ];
            })->values()->all()
            : [
                [
                    'Nama' => '-',
                    'harga awal' => 0,
                    'Jumlah' => 0,
                    'harga' => 0,
                ]
            ];

        // Menghitung total semua harga resep pulang
        $totalresepPulang = $pasien->resepPulang ? $pasien->resepPulang->sum('total') : 0;

        $register = $pasien->biaya_reg;

        $totalRalan = $totalBiayaKonsultasiDokterRalan
            + $totalBiayaVisteSpesialisRalan
            + $totalBiayaVisiteUmumRalan
            + $totalpemeriksaanRalan
            + $totalTindakanRalan
            + $totalperawatanRalan
            + $totalLabRalan
            + $totalDetailLabRalan
            + $totalradiologiRalan
            + $totaloperasiStatusRalan
            + $totalobatRalan
            + $register;

        $totalRanap = $totalBiayaVisteRanap
            + $totalBiayaPemeriksaanRanap
            + $totalBiayaTindakanRanap
            + $totalBiayaperawatanRanap
            + $totalLabRanap
            + $totalDetailLabRanap
            + $totalradiologiRanap
            + $totaloperasiStatusRanap
            + $totalobatRanap + $totalBiayaKamar
            + $totalresepPulang;

        $totalSemua = $totalRalan + $totalRanap;

        $data_pasien[] = [
            'no_rawat' => $pasien->no_rawat,
            'bed' => $bangsalKamar,
            'lama' =>  $hasilPeriode,
            'no_rkm_medis' => $pasien->no_rkm_medis,
            'nm_pasien' => $pasien->pasien->nm_pasien ?? "tidak tahu",
            'alamat' => $pasien->almt_pj ?? "tidak tahu",
            'dpjp' => $dokter_dpjp,
            'dokterIgd' => $dokter_igd,
            'asalPermintaan' => $pasien->poliklinik->nm_poli,
            'register' => $register,
            'kamar' =>  $hargaKamar,
            'totalHrgKamar' => $totalBiayaKamar,

            'KonsultasiDokterRalan' => $konsultasiDokterRalan,
            'TotalHargaKonsultasiDokterRalan' => $totalBiayaKonsultasiDokterRalan,
            'KonsultasiDokterRanap' => $konsultasiDokterRanap,
            'TotalHargaKonsultasiDokterRanap' => $totalBiayaKonsultasiDokterRanap,
            'KonsultasiDokterPerawatRanap' => $konsultasiDokterPerawatRanap,
            'TotalHargaKonsultasiDokterPerawatRanap' => $totalBiayaKonsultasikonsultasiDokterPerawatRanap,
            'TotalBiayaKonsultasi' => $totalBiayaKonsultasi,

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

            'KunjunganDokterRanap' => $kunjunganDokter,
            'TotalBiayaDokterRanap' => $totalBiayaVisiteDokter,
            'KunjunganDokterPerawatRanap' => $kunjunganDokterPerawat,
            'TotalBiayaDokterPerawatRanap' =>  $totalBiayaVisiteDokterPerawat,
            'TotalBiayaVisiteRanap' => $totalBiayaVisteRanap,
            'TotalBiayaVisite' => $totalBiayaViste,

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

            //lab
            'Lab' => $labData,
            'TotalLabRalan' => $totalLabRalan,
            'TotalLabRanap' => $totalLabRanap,
            'TotalLab' => $totalLab,

            //detail Lab
            'DetailLab' => $detailLab,
            'TotalDetailLabRalan' => $totalDetailLabRalan,
            'TotalDetailLabRanap' => $totalDetailLabRanap,
            'TotalDetailLab' => $totalDetailLab,
            'TotalSemuaLab' =>  $totalSemuaLab,

            // Radio
            'Radiologi' => $radiologi,
            'TotalRadiologiRalan' => $totalradiologiRalan,
            'TotalRadiologiRanap' => $totalradiologiRanap,
            'TotalRadiologi' => $totalradiologi,

            //Operasi
            'Operasi' => $operasiStatus,
            'TotalOperasiRalan' => $totaloperasiStatusRalan,
            'TotalOperasiRanap' => $totaloperasiStatusRanap,
            'TotalOperasi' => $totaloperasiStatus,

            //Obat
            'Obat' => $obat,
            'TotalobatRalan' => $totalobatRalan,
            'TotalobatRanap' => $totalobatRanap,
            'Totalobat' => $totalobat,

            //Resep Pulang
            'ResepPulang' => $resepPulang,
            'totalresepPulang' => $totalresepPulang,

            'totalRalan' => $totalRalan,
            'totalRanap' => $totalRanap,
            'totalSemua' => $totalSemua,
        ];

        // return response()->json([$data_pasien]);

        return view('laporanBilling', compact('no_rawat', 'data_pasien'));
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
            'periksaRadiologi.jnsPerawatan',
            'hemodialisa',
            'rawatInapDrpr.JnsPerawatanInap',
            'obat.barang',
            'resepPulang.barang',
        ])->where('no_rawat', $no_rawat)->first();
    }

    private function responseNotFound()
    {
        abort(404, 'Data pasien tidak ditemukan');
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
        if (!is_array($kamarTerakhir) || !isset($kamarTerakhir['bed'], $kamarTerakhir['bangsal'])) {
            return 'Data kamar tidak tersedia';
        }

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
        $data = $pasien->rawatJlDr
            ->filter(function ($rawatJlDr) use ($kategori) {
                return isset($rawatJlDr->kode) && $rawatJlDr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlDr) {
                return [
                    'kode' => $rawatJlDr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlDr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlDr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlDr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatJlDr->biaya_rawat ?? 0, // Pastikan defaultnya 0 agar bisa dijumlahkan
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }

    private function getDokterPerawatRalan($pasien, $kategori)
    {
        $data = $pasien->rawatJlDrpr
            ->filter(function ($rawatJlDrpr) use ($kategori) {
                return isset($rawatJlDrpr->kode) && $rawatJlDrpr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlDrpr) {
                return [
                    'kode' => $rawatJlDrpr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlDrpr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlDrpr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlDrpr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatJlDrpr->biaya_rawat ?? 0, // Pastikan defaultnya 0 agar bisa dijumlahkan
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }

    private function getDokterRanap($pasien, $kategori)
    {
        $data = $pasien->rawatinapdr
            ->filter(function ($rawatinapdr) use ($kategori) {
                return isset($rawatinapdr->kode) && $rawatinapdr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatinapdr) {
                return [
                    'kode' => $rawatinapdr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatinapdr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatinapdr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatinapdr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatinapdr->biaya_rawat ?? 0, // Ubah '-' menjadi 0 agar bisa dijumlahkan
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }


    private function getDokterPerawatRanap($pasien, $kategori)
    {
        $data = $pasien->rawatInapDrpr
            ->filter(function ($rawatInapDrpr) use ($kategori) {
                return isset($rawatInapDrpr->JnsPerawatanInap) && $rawatInapDrpr->JnsPerawatanInap->kd_kategori === $kategori;
            })
            ->map(function ($rawatInapDrpr) {
                return [
                    'kode' => $rawatInapDrpr->JnsPerawatanInap->kd_jenis_prw ?? '-',
                    'nama' => $rawatInapDrpr->JnsPerawatanInap->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatInapDrpr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatInapDrpr->dokter->nm_dokter ?? '-',
                    "biaya" => $rawatInapDrpr->biaya_rawat ?? 0,
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }

    private function getPerawatRanap($pasien, $kategori)
    {
        $data = $pasien->rawatInapPr
            ->filter(function ($rawatInapPr) use ($kategori) {
                return isset($rawatInapPr->kode) && $rawatInapPr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatInapPr) {
                return [
                    'kode' => $rawatInapPr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatInapPr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatInapPr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatInapPr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatInapPr->biaya_rawat ?? 0, // Ubah '-' menjadi 0 agar bisa dijumlahkan
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }

    private function getPerawatRalan($pasien, $kategori)
    {
        $data = $pasien->rawatJlPr
            ->filter(function ($rawatJlPr) use ($kategori) {
                return isset($rawatJlPr->kode) && $rawatJlPr->kode->kd_kategori === $kategori;
            })
            ->map(function ($rawatJlPr) {
                return [
                    'kode' => $rawatJlPr->kode->kd_jenis_prw ?? '-',
                    'nama' => $rawatJlPr->kode->nm_perawatan ?? '-',
                    'kodeDokter' => $rawatJlPr->dokter->kd_dokter ?? '-',
                    'dokter' => $rawatJlPr->dokter->nm_dokter ?? '-',
                    'biaya' => $rawatJlPr->biaya_rawat ?? 0, // Ubah '-' menjadi 0 agar bisa dijumlahkan
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

        // Jika tidak ada data, kembalikan nilai default
        return $data->isEmpty() ? collect([[
            'kode' => '-',
            'nama' => '-',
            'kodeDokter' => '-',
            'dokter' => '-',
            'biaya' => 0,
            'jumlah' => 0,
            'totalBiaya' => 0
        ]]) : $data;
    }

    private function getTotalBiaya($total)
    {
        return $total->sum('totalBiaya');
    }
}
