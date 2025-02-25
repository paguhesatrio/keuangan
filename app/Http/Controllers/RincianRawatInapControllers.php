<?php

namespace App\Http\Controllers;

use App\Models\Bangsal;
use App\Models\RegPeriksa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Common\Entity\Row;

class RincianRawatInapControllers extends Controller
{
    public function RincianRawatInap(Request $request)
    {
        $tgl_keluar_start = $request->input('tgl_keluar_start', date('Y-m-d')); // Default ke awal bulan
        $tgl_keluar_end = $request->input('tgl_keluar_end', date('Y-m-d')); // Default ke hari ini

        // Ambil kode bangsal dari session
        $kode_bangsal = session('kode_bangsal'); // Mengambil kode bangsal yang disimpan dalam session

        // Ambil semua kode bangsal
        $bangsalList = Bangsal::all();

        // Query pasien berdasarkan tanggal dan kode bangsal
        $pasienList = RegPeriksa::with([
            'pasien',
            'sep',
            'dpjp.dokter',
            'operasi.dokter1',
            'operasi.dokter2',
            'operasi.dokter3',
            'operasi.anestesi',
            'rawatinapdr.dokter',
            'rawatinapdr.kode',
            'rawatInapPr.kode',
            'kamarinap.kamar.bangsal',
            'periksalab',
            'periksaRadiologi',
            'hemodialisa',
            'rawatInapDrpr.JnsPerawatanInap',
        ])
            ->whereHas('kamarinap.kamar.bangsal', function ($query) use ($kode_bangsal) {
                if ($kode_bangsal) {
                    $query->where('kd_bangsal', $kode_bangsal); // Filter berdasarkan kode bangsal
                }
            })
            ->whereHas('kamarinap', function ($query) use ($tgl_keluar_start, $tgl_keluar_end) {
                $query->whereBetween('tgl_keluar', [$tgl_keluar_start, $tgl_keluar_end]); // Filter berdasarkan rentang tanggal
            })
            ->get();

        // Memeriksa apakah ada pasien yang ditemukan
        if ($pasienList->isEmpty()) {
            return view('home', [
                'data_pasien' => [],
                'bangsalList' => $bangsalList,
                'tgl_keluar_start' => $tgl_keluar_start,
                'tgl_keluar_end' => $tgl_keluar_end,
                'kode_bangsal' => $kode_bangsal,
                'message' => 'Data tidak ditemukan',
            ]);
        }
        $data_pasien = []; // Untuk menyimpan semua data pasien

        foreach ($pasienList as $pasien) {
            if ($pasien && $pasien->dpjp && $pasien->sep && $pasien->sep->no_sep) {

                $allowed_kode_rawat = ['RI00072', 'RI00073', 'RI00074', 'RI00075'];
                $dokter_list = $pasien->rawatinapdr
                    ->filter(function ($rawat) use ($allowed_kode_rawat) {
                        return $rawat->kode && in_array($rawat->kode->kd_jenis_prw, $allowed_kode_rawat);
                    })
                    ->map(function ($rawat) {
                        return [
                            'nama_dokter' => $rawat->dokter->nm_dokter ?? 'Tidak Diketahui Perawtan', // Default jika nama dokter tidak ada
                            'kd_jenis_prw' => $rawat->kode->kd_jenis_prw ?? 'Tidak Diketahui Nama Dokter',
                            'nm_perawatan' => $rawat->kode->nm_perawatan ?? 'Tidak Diketahui Perawtan',
                        ];
                    });

                $grouped_dokter = $dokter_list->groupBy(function ($item) {
                    return $item['nama_dokter'] . '|' . $item['kd_jenis_prw'];
                })->map(function ($items, $key) {
                    [$nama_dokter, $kd_jenis_prw] = explode('|', $key);

                    $jumlah_kode = $items->count();
                    $nm_perawatan = $items->pluck('nm_perawatan')->first();

                    return [
                        'nama' => $nm_perawatan ?? 'Tidak Diketahui Nama Perawtan',
                        'kd_jenis_prw' => $kd_jenis_prw ?? 'Tidak Diketahui Kode',
                        'nama_dokter' => $nama_dokter ?? 'Tidak Diketahui Nama Dokter',
                        'jumlah_kode' => $jumlah_kode ?? 'Tidak Diketahui Jumlah Kode',
                    ];
                });

                $nama_kunjungan = $grouped_dokter->pluck('nama')->toArray(); // Hanya nama perawatan
                $nama_dokter = $grouped_dokter->pluck('nama_dokter')->toArray(); // Hanya nama dokter
                $jumlah_kunjungan = $grouped_dokter->pluck('jumlah_kode')->toArray(); // Hanya jumlah kode

                $dokter_dpjp = $pasien->dpjp->first()->dokter->nm_dokter ?? '-';

                //Operasi
                $operasiGroup = $pasien->operasi && !$pasien->operasi->isEmpty()
                    ? $pasien->operasi
                    ->groupBy(function ($operasi) {
                        return $operasi->tgl_operasi; // Kelompokkan berdasarkan tgl_operasi
                    })
                    ->map(function ($group, $tgl_operasi) {
                        $dokter1 = $group->first()->dokter1->nm_dokter ?? '-';
                        $dokter2 = $group->first()->dokter2->nm_dokter ?? '-';
                        $dokter3 = $group->first()->dokter3->nm_dokter ?? '-';
                        $anestesi = $group->first()->anestesi->nm_dokter ?? '-';

                        // Cek apakah semua data kosong
                        $status_operasi = ($dokter1 === '-' && $dokter2 === '-' && $dokter3 === '-' && $anestesi === '-')
                            ? 'Tidak Ada'
                            : 'Ada';

                        return [
                            'Tanggal Operasi' => $tgl_operasi,
                            'Dokter 1' => $dokter1,
                            'Dokter 2' => $dokter2,
                            'Dokter 3' => $dokter3,
                            'Anestesi' => $anestesi,
                            'Status Operasi' => $status_operasi,
                        ];
                    })
                    ->values()
                    : collect([
                        [
                            'Tanggal Operasi' => '-',
                            'Dokter 1' => '-',
                            'Dokter 2' => '-',
                            'Dokter 3' => '-',
                            'Anestesi' => '-',
                            'Status Operasi' => 'Tidak Ada',
                        ],
                    ]);

                $operasiStatus = $operasiGroup->pluck('Status Operasi')->unique()->implode(', ');
                $tglOperasi = $operasiGroup->pluck('Tanggal Operasi')->toArray();
                $dokter1 = $operasiGroup->pluck('Dokter 1')->toArray();
                $dokter2 = $operasiGroup->pluck('Dokter 2')->toArray();
                $dokter3 = $operasiGroup->pluck('Dokter 3')->toArray();
                $anestesi = $operasiGroup->pluck('Anestesi')->toArray();

                $kamar_data = $pasien->kamarinap->map(function ($kamarInap) {
                    return [
                        'bed' => $kamarInap->kd_kamar ?? '-',
                        'bangsal' => $kamarInap->kamar->bangsal->nm_bangsal ?? '-',
                        'lama' => $kamarInap->lama ?? '-',
                    ];
                });

                $bed = $kamar_data->pluck('bed')->toArray();
                $bangsal = $kamar_data->pluck('bangsal')->toArray();
                $lama = $kamar_data->pluck('lama')->toArray();


                $lab_data = $pasien->periksalab && !$pasien->periksalab->isEmpty()
                    ? $pasien->periksalab
                    ->groupBy(function ($lab) {
                        return $lab->dokter->nm_dokter ?? '-';
                    }) // Kelompokkan berdasarkan nama dokter
                    ->map(function ($group, $dokter) {
                        $uniqueRujukan = $group->groupBy(function ($item) {
                            return $item->tgl_periksa . ' ' . $item->jam; // Gabungkan tanggal dan jam
                        })->count(); // Hitung jumlah unik berdasarkan tanggal dan jam

                        return [
                            'Status' => 'Ada',
                            'Dokter perujuk' => $dokter,
                            'Jumlah rujukan' => $uniqueRujukan, // Hitung jumlah unik
                        ];
                    })
                    ->values() // Menyusun ulang indeks
                    : collect([
                        [
                            'Status' => 'Tidak ada',
                            'Dokter perujuk' => '-',
                            'Jumlah rujukan' => 0,
                        ],
                    ]);

                $dokterLab = $lab_data->pluck('Dokter perujuk')->toArray();
                $jumlahlab = $lab_data->pluck('Jumlah rujukan')->toArray();

                $radiologi_data = $pasien->periksaRadiologi && !$pasien->periksaRadiologi->isEmpty()
                    ? $pasien->periksaRadiologi
                    ->groupBy(function ($radio) {
                        return $radio->dokter->nm_dokter ?? '-';
                    }) // Kelompokkan berdasarkan nama dokter
                    ->map(function ($group, $dokter) {
                        return [
                            'Status' => 'Ada',
                            'Dokter perujuk' => $dokter,
                            'Jumlah rujukan' => $group->count(), // Hitung jumlah rujukan
                        ];
                    })
                    ->values() // Menyusun ulang indeks
                    : collect([ // Ubah menjadi koleksi untuk memudahkan manipulasi
                        [
                            'Status' => 'Tidak ada',
                            'Dokter perujuk' => '-',
                            'Jumlah rujukan' => 0,
                        ],
                    ]);

                $dokterRadiologi = $radiologi_data->pluck('Dokter perujuk')->toArray(); // Memanggil 'Dokter perujuk' dari koleksi
                $jumlahRadiologi = $radiologi_data->pluck('Jumlah rujukan')->toArray(); // Memanggil 'Jumlah rujukan' dari koleksi

                // $hd_data = in_array("RI00071", [
                //     $pasien->rawatInapDr ?? null,
                //     $pasien->rawatInapDrPr ?? null,
                //     $pasien->rawatInapPr ?? null,
                // ]) ? "Ada" : "Tidak Ada";

                $hd_data = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00071') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00071') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00071'))
                    ? "Ada" : "Tidak Ada";

                $endoskopi_data = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00126') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00126') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00126'))
                    ? "Ada" : "Tidak Ada";

                $ekokardiografi = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00093') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00093') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00093'))
                    ? "Ada" : "Tidak Ada";;

                $venti_data = collect([$pasien->rawatInapDr, $pasien->rawatInapDrPr, $pasien->rawatInapPr])
                    ->flatten()
                    ->filter(function ($ventilator) {
                        return $ventilator->JnsPerawatanInap && $ventilator->JnsPerawatanInap->kd_jenis_prw === 'RI00034';
                    })
                    ->all();

                $venti_in_icu = 'Tidak ada';

                if (!empty($venti_data) && !empty($pasien->kamarinap)) {
                    foreach ($pasien->kamarinap as $kamar) {
                        if ($kamar->kamar->bangsal->kd_bangsal === 'ICU') {
                            foreach ($venti_data as $ventilator) {
                                $venti_date = $ventilator->tgl_perawatan;
                                $venti_time = $ventilator->jam_rawat;

                                if (
                                    $venti_date === $kamar->tgl_masuk &&
                                    $venti_time >= $kamar->jam_masuk &&
                                    ($venti_date < $kamar->tgl_keluar || $venti_time <= $kamar->jam_keluar)
                                ) {
                                    $venti_in_icu = 'Ada';
                                    break 2;
                                }
                            }
                        }
                    }
                }

                $intubasi_data = collect([$pasien->rawatInapDr, $pasien->rawatInapDrPr, $pasien->rawatInapPr])
                    ->flatten()
                    ->filter(function ($intubasi) {
                        return $intubasi->JnsPerawatanInap && $intubasi->JnsPerawatanInap->kd_jenis_prw === 'RI00090';
                    })
                    ->all();

                $intubasi_in_icu = 'Tidak';
                $dokter_intubasi = [];

                if (!empty($intubasi_data) && !empty($pasien->kamarinap)) {
                    foreach ($pasien->kamarinap as $kamar) {
                        // Pastikan bangsal adalah ICU
                        if ($kamar->kamar->bangsal->kd_bangsal === 'ICU') {
                            foreach ($intubasi_data as $intubasi) {
                                $intubasi_date = $intubasi->tgl_perawatan;
                                $intubasi_time = $intubasi->jam_rawat;

                                if (
                                    $intubasi_date === $kamar->tgl_masuk &&
                                    $intubasi_time >= $kamar->jam_masuk &&
                                    ($intubasi_date < $kamar->tgl_keluar || $intubasi_time <= $kamar->jam_keluar)
                                ) {
                                    $intubasi_in_icu = 'Ada';

                                    // Menyimpan data dokter
                                    $dokter_intubasi[] = [
                                        'kd_dokter' => $intubasi->kd_dokter,
                                        'nama_dokter' => optional($intubasi->dokter)->nm_dokter ?? '-'
                                    ];
                                }
                            }
                        }
                    }
                }

                // Hilangkan duplikat data dokter (jika diperlukan)
                $dokter_intubasi = collect($dokter_intubasi)->unique('kd_dokter')->values()->all();

                // Format outputz
                $intubasi_result = [
                    'status' => $intubasi_in_icu,
                    'dokter' => $dokter_intubasi,
                ];

                $dokter_intu = collect($dokter_intubasi)->pluck('nama_dokter')->join(', ');

                $data_pasien[] = [
                    'no_rawat' => $pasien->no_rawat,
                    'no_rkm_medis' => $pasien->no_rkm_medis,
                    'sep' => substr($pasien->sep->no_sep, -4),
                    'nm_pasien' => $pasien->pasien->nm_pasien ?? 'Tidak Diketahui Nama Pasien',
                    'dokter_dpjp' => $dokter_dpjp,


                    'operasi' => $operasiGroup->values(),
                    'tanggal' => $tglOperasi,
                    'dokter1' => $dokter1,
                    'dokter2' => $dokter2,
                    'dokter3' => $dokter3,
                    'anestesi' => $anestesi,
                    'operasiStatus' => $operasiStatus,

                    'kunjungan' => $grouped_dokter->values(),
                    'Nama Kunjungan' => $nama_kunjungan,
                    'Nama Dokter Kunjungan' => $nama_dokter,
                    'Jumlah Kunjungan' => $jumlah_kunjungan,

                    'kamar' => $kamar_data,
                    'bed' => $bed,
                    'bangsal' => $bangsal,
                    'lama' => $lama,

                    'lab' => $lab_data,
                    'dokterlab' => $dokterLab,
                    'jumlahlab' => $jumlahlab,

                    'radiologi' => $radiologi_data,
                    'dokterRadio' => $dokterRadiologi,
                    'jumlahRadio' => $jumlahRadiologi,

                    'hd' => $hd_data,
                    'endoskopi' => $endoskopi_data,
                    'ekokardiografi' => $ekokardiografi,
                    'venti' => $venti_data,
                    'venti in icu' => $venti_in_icu,
                    'intubasi in icu' => $intubasi_result,
                    'dokter intubasi' =>  $dokter_intu,
                ];
            }
        }

        session(['data_pasien' => $data_pasien]);
        return view('home', compact('tgl_keluar_start', 'tgl_keluar_end', 'kode_bangsal', 'bangsalList', 'data_pasien'));
    }

    public function RincianRawatInapAdmin(Request $request)
    {
        $tgl_keluar_start = $request->input('tgl_keluar_start', date('Y-m-d')); // Default ke awal bulan
        $tgl_keluar_end = $request->input('tgl_keluar_end', date('Y-m-d')); // Default ke hari ini
        $kode_bangsal = $request->input('kode_bangsal');

        // Ambil semua kode bangsal
        $bangsalList = Bangsal::all();

        // Query pasien berdasarkan tanggal dan kode bangsal
        $pasienList = RegPeriksa::with([
            'pasien',
            'sep',
            'dpjp.dokter',
            'operasi.dokter1',
            'operasi.dokter2',
            'operasi.dokter3',
            'operasi.anestesi',
            'rawatinapdr.dokter',
            'rawatinapdr.kode',
            'rawatInapPr.kode',
            'kamarinap.kamar.bangsal',
            'periksalab',
            'periksaRadiologi',
            'hemodialisa',
            'rawatInapDrpr.JnsPerawatanInap',
        ])
            ->whereHas('kamarinap.kamar.bangsal', function ($query) use ($kode_bangsal) {
                if ($kode_bangsal) {
                    $query->where('kd_bangsal', $kode_bangsal); // Filter berdasarkan kode bangsal
                }
            })
            ->whereHas('kamarinap', function ($query) use ($tgl_keluar_start, $tgl_keluar_end) {
                $query->whereBetween('tgl_keluar', [$tgl_keluar_start, $tgl_keluar_end]); // Filter berdasarkan rentang tanggal
            })
            ->get();

        // Memeriksa apakah ada pasien yang ditemukan
        if ($pasienList->isEmpty()) {
            return view('admin', [
                'data_pasien' => [],
                'bangsalList' => $bangsalList,
                'tgl_keluar_start' => $tgl_keluar_start,
                'tgl_keluar_end' => $tgl_keluar_end,
                'kode_bangsal' => $kode_bangsal,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $data_pasien = []; // Untuk menyimpan semua data pasien

        foreach ($pasienList as $pasien) {
            if ($pasien && $pasien->dpjp && $pasien->sep && $pasien->sep->no_sep) {

                $allowed_kode_rawat = ['RI00072', 'RI00073', 'RI00074', 'RI00075'];
                $dokter_list = $pasien->rawatinapdr
                    ->filter(function ($rawat) use ($allowed_kode_rawat) {
                        return $rawat->kode && in_array($rawat->kode->kd_jenis_prw, $allowed_kode_rawat);
                    })
                    ->map(function ($rawat) {
                        return [
                            'nama_dokter' => $rawat->dokter->nm_dokter ?? 'Tidak Diketahui Perawtan', // Default jika nama dokter tidak ada
                            'kd_jenis_prw' => $rawat->kode->kd_jenis_prw ?? 'Tidak Diketahui Nama Dokter',
                            'nm_perawatan' => $rawat->kode->nm_perawatan ?? 'Tidak Diketahui Perawtan',
                        ];
                    });

                $grouped_dokter = $dokter_list->groupBy(function ($item) {
                    return $item['nama_dokter'] . '|' . $item['kd_jenis_prw'];
                })->map(function ($items, $key) {
                    [$nama_dokter, $kd_jenis_prw] = explode('|', $key);

                    $jumlah_kode = $items->count();
                    $nm_perawatan = $items->pluck('nm_perawatan')->first();

                    return [
                        'nama' => $nm_perawatan ?? 'Tidak Diketahui Nama Perawtan',
                        'kd_jenis_prw' => $kd_jenis_prw ?? 'Tidak Diketahui Kode',
                        'nama_dokter' => $nama_dokter ?? 'Tidak Diketahui Nama Dokter',
                        'jumlah_kode' => $jumlah_kode ?? 'Tidak Diketahui Jumlah Kode',
                    ];
                });

                $nama_kunjungan = $grouped_dokter->pluck('nama')->toArray(); // Hanya nama perawatan
                $nama_dokter = $grouped_dokter->pluck('nama_dokter')->toArray(); // Hanya nama dokter
                $jumlah_kunjungan = $grouped_dokter->pluck('jumlah_kode')->toArray(); // Hanya jumlah kode

                $dokter_dpjp = $pasien->dpjp->first()->dokter->nm_dokter ?? '-';


                //Operasi
                $operasiGroup = $pasien->operasi && !$pasien->operasi->isEmpty()
                    ? $pasien->operasi
                    ->groupBy(function ($operasi) {
                        return $operasi->tgl_operasi; // Kelompokkan berdasarkan tgl_operasi
                    })
                    ->map(function ($group, $tgl_operasi) {
                        $dokter1 = $group->first()->dokter1->nm_dokter ?? '-';
                        $dokter2 = $group->first()->dokter2->nm_dokter ?? '-';
                        $dokter3 = $group->first()->dokter3->nm_dokter ?? '-';
                        $anestesi = $group->first()->anestesi->nm_dokter ?? '-';

                        // Cek apakah semua data kosong
                        $status_operasi = ($dokter1 === '-' && $dokter2 === '-' && $dokter3 === '-' && $anestesi === '-')
                            ? 'Tidak Ada'
                            : 'Ada';

                        return [
                            'Tanggal Operasi' => $tgl_operasi,
                            'Dokter 1' => $dokter1,
                            'Dokter 2' => $dokter2,
                            'Dokter 3' => $dokter3,
                            'Anestesi' => $anestesi,
                            'Status Operasi' => $status_operasi,
                        ];
                    })
                    ->values()
                    : collect([
                        [
                            'Tanggal Operasi' => '-',
                            'Dokter 1' => '-',
                            'Dokter 2' => '-',
                            'Dokter 3' => '-',
                            'Anestesi' => '-',
                            'Status Operasi' => 'Tidak Ada',
                        ],
                    ]);

                $operasiStatus = $operasiGroup->pluck('Status Operasi')->unique()->implode(', ');
                $tglOperasi = $operasiGroup->pluck('Tanggal Operasi')->toArray();
                $dokter1 = $operasiGroup->pluck('Dokter 1')->toArray();
                $dokter2 = $operasiGroup->pluck('Dokter 2')->toArray();
                $dokter3 = $operasiGroup->pluck('Dokter 3')->toArray();
                $anestesi = $operasiGroup->pluck('Anestesi')->toArray();

                $kamar_data = $pasien->kamarinap->map(function ($kamarInap) {
                    return [
                        'bed' => $kamarInap->kd_kamar ?? '-',
                        'bangsal' => $kamarInap->kamar->bangsal->nm_bangsal ?? '-',
                        'lama' => $kamarInap->lama ?? '-',
                    ];
                });

                $bed = $kamar_data->pluck('bed')->toArray();
                $bangsal = $kamar_data->pluck('bangsal')->toArray();
                $lama = $kamar_data->pluck('lama')->toArray();


                $lab_data = $pasien->periksalab && !$pasien->periksalab->isEmpty()
                    ? $pasien->periksalab
                    ->groupBy(function ($lab) {
                        return $lab->dokter->nm_dokter ?? '-';
                    }) // Kelompokkan berdasarkan nama dokter
                    ->map(function ($group, $dokter) {
                        $uniqueRujukan = $group->groupBy(function ($item) {
                            return $item->tgl_periksa . ' ' . $item->jam; // Gabungkan tanggal dan jam
                        })->count(); // Hitung jumlah unik berdasarkan tanggal dan jam

                        return [
                            'Status' => 'Ada',
                            'Dokter perujuk' => $dokter,
                            'Jumlah rujukan' => $uniqueRujukan, // Hitung jumlah unik
                        ];
                    })
                    ->values() // Menyusun ulang indeks
                    : collect([
                        [
                            'Status' => 'Tidak ada',
                            'Dokter perujuk' => '-',
                            'Jumlah rujukan' => 0,
                        ],
                    ]);

                $dokterLab = $lab_data->pluck('Dokter perujuk')->toArray();
                $jumlahlab = $lab_data->pluck('Jumlah rujukan')->toArray();

                $radiologi_data = $pasien->periksaRadiologi && !$pasien->periksaRadiologi->isEmpty()
                    ? $pasien->periksaRadiologi
                    ->groupBy(function ($radio) {
                        return $radio->dokter->nm_dokter ?? '-';
                    }) // Kelompokkan berdasarkan nama dokter
                    ->map(function ($group, $dokter) {
                        return [
                            'Status' => 'Ada',
                            'Dokter perujuk' => $dokter,
                            'Jumlah rujukan' => $group->count(), // Hitung jumlah rujukan
                        ];
                    })
                    ->values() // Menyusun ulang indeks
                    : collect([ // Ubah menjadi koleksi untuk memudahkan manipulasi
                        [
                            'Status' => 'Tidak ada',
                            'Dokter perujuk' => '-',
                            'Jumlah rujukan' => 0,
                        ],
                    ]);

                $dokterRadiologi = $radiologi_data->pluck('Dokter perujuk')->toArray(); // Memanggil 'Dokter perujuk' dari koleksi
                $jumlahRadiologi = $radiologi_data->pluck('Jumlah rujukan')->toArray(); // Memanggil 'Jumlah rujukan' dari koleksi

                $hd_data = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00071') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00071') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00071'))
                    ? "Ada" : "Tidak Ada";

                $endoskopi_data = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00126') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00126') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00126'))
                    ? "Ada" : "Tidak Ada";

                $ekokardiografi = (collect($pasien->rawatInapDr)->contains('kd_jenis_prw', 'RI00093') ||
                    collect($pasien->rawatInapDrPr)->contains('kd_jenis_prw', 'RI00093') ||
                    collect($pasien->rawatInapPr)->contains('kd_jenis_prw', 'RI00093'))
                    ? "Ada" : "Tidak Ada";;

                $venti_data = collect([$pasien->rawatInapDr, $pasien->rawatInapDrPr, $pasien->rawatInapPr])
                    ->flatten()
                    ->filter(function ($ventilator) {
                        return $ventilator->JnsPerawatanInap && $ventilator->JnsPerawatanInap->kd_jenis_prw === 'RI00034';
                    })
                    ->all();

                $venti_in_icu = 'Tidak ada';

                if (!empty($venti_data) && !empty($pasien->kamarinap)) {
                    foreach ($pasien->kamarinap as $kamar) {
                        if ($kamar->kamar->bangsal->kd_bangsal === 'ICU') {
                            foreach ($venti_data as $ventilator) {
                                $venti_date = $ventilator->tgl_perawatan;
                                $venti_time = $ventilator->jam_rawat;

                                if (
                                    $venti_date === $kamar->tgl_masuk &&
                                    $venti_time >= $kamar->jam_masuk &&
                                    ($venti_date < $kamar->tgl_keluar || $venti_time <= $kamar->jam_keluar)
                                ) {
                                    $venti_in_icu = 'Ada';
                                    break 2;
                                }
                            }
                        }
                    }
                }

                $intubasi_data = collect([$pasien->rawatInapDr, $pasien->rawatInapDrPr, $pasien->rawatInapPr])
                    ->flatten()
                    ->filter(function ($intubasi) {
                        return $intubasi->JnsPerawatanInap && $intubasi->JnsPerawatanInap->kd_jenis_prw === 'RI00090';
                    })
                    ->all();

                $intubasi_in_icu = 'Tidak';
                $dokter_intubasi = [];

                if (!empty($intubasi_data) && !empty($pasien->kamarinap)) {
                    foreach ($pasien->kamarinap as $kamar) {
                        // Pastikan bangsal adalah ICU
                        if ($kamar->kamar->bangsal->kd_bangsal === 'ICU') {
                            foreach ($intubasi_data as $intubasi) {
                                $intubasi_date = $intubasi->tgl_perawatan;
                                $intubasi_time = $intubasi->jam_rawat;

                                if (
                                    $intubasi_date === $kamar->tgl_masuk &&
                                    $intubasi_time >= $kamar->jam_masuk &&
                                    ($intubasi_date < $kamar->tgl_keluar || $intubasi_time <= $kamar->jam_keluar)
                                ) {
                                    $intubasi_in_icu = 'Ada';

                                    // Menyimpan data dokter
                                    $dokter_intubasi[] = [
                                        'kd_dokter' => $intubasi->kd_dokter,
                                        'nama_dokter' => optional($intubasi->dokter)->nm_dokter ?? '-'
                                    ];
                                }
                            }
                        }
                    }
                }

                // Hilangkan duplikat data dokter (jika diperlukan)
                $dokter_intubasi = collect($dokter_intubasi)->unique('kd_dokter')->values()->all();

                // Format outputz
                $intubasi_result = [
                    'status' => $intubasi_in_icu,
                    'dokter' => $dokter_intubasi,
                ];

                $dokter_intu = collect($dokter_intubasi)->pluck('nama_dokter')->join(', ');

                $data_pasien[] = [
                    'no_rawat' => $pasien->no_rawat,
                    'no_rkm_medis' => $pasien->no_rkm_medis,
                    'sep' => substr($pasien->sep->no_sep, -4),
                    'nm_pasien' => $pasien->pasien->nm_pasien ?? 'Tidak Diketahui Nama Pasien',
                    'dokter_dpjp' => $dokter_dpjp,

                    'operasi' => $operasiGroup->values(),
                    'tanggal' => $tglOperasi,
                    'dokter1' => $dokter1,
                    'dokter2' => $dokter2,
                    'dokter3' => $dokter3,
                    'anestesi' => $anestesi,
                    'operasiStatus' => $operasiStatus,

                    'kunjungan' => $grouped_dokter->values(),
                    'Nama Kunjungan' => $nama_kunjungan,
                    'Nama Dokter Kunjungan' => $nama_dokter,
                    'Jumlah Kunjungan' => $jumlah_kunjungan,

                    'kamar' => $kamar_data,
                    'bed' => $bed,
                    'bangsal' => $bangsal,
                    'lama' => $lama,

                    'lab' => $lab_data,
                    'dokterlab' => $dokterLab,
                    'jumlahlab' => $jumlahlab,

                    'radiologi' => $radiologi_data,
                    'dokterRadio' => $dokterRadiologi,
                    'jumlahRadio' => $jumlahRadiologi,

                    'hd' => $hd_data,
                    'endoskopi' => $endoskopi_data,
                    'ekokardiografi' => $ekokardiografi,
                    'venti' => $venti_data,
                    'venti in icu' => $venti_in_icu,
                    'intubasi in icu' => $intubasi_result,
                    'dokter intubasi' =>  $dokter_intu,
                ];
            }
        }

        session(['data_pasien' => $data_pasien]);
        return view('admin', compact('tgl_keluar_start', 'tgl_keluar_end', 'kode_bangsal', 'bangsalList', 'data_pasien'));
    }

    public function print()
    {
        $data_pasien = session('data_pasien', []);

        if (empty($data_pasien)) {
            return redirect()->back()->with('message', 'Data tidak ditemukan untuk diunduh');
        }

        $pdf = PDF::loadView('print', ['data_pasien' => $data_pasien])
            ->setPaper([0, 0, 1728, 2592], 'landscape') // 24 inci x 36 inci
            ->setWarnings(false);

        return $pdf->download('rincian_rawat_inap.pdf');
    }

    public function exportExcel()
    {
        $data_pasien = session('data_pasien', []);

        if (empty($data_pasien)) {
            return redirect()->back()->with('message', 'Data tidak ditemukan untuk diunduh');
        }

        $filePath = storage_path('app/public/rincian_rawat_inap.xlsx');
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        // Tambahkan header
        $header = WriterEntityFactory::createRowFromArray([
            'No Rawat',
            'No Rekam Medis',
            'No Sep',
            'Nama Pasien',
            'Nama Dokter DPJP',
            'Operasi Status',
            'Tanggal Operasi',
            'Dokter Operasi 1',
            'Dokter Operasi 2',
            'Dokter Operasi 3',
            'Dokter Anestesi',
            'Nama Kunjungan',
            'Nama Dokter Berkunjung',
            'Jumlah Kunjungan',
            'Bed',
            'Bangsal',
            'Lama Menginap',
            'Dokter Perujuk Lab',
            'Jumlah Rujukan Lab',
            'Dokter Perujuk Radiologi',
            'Jumlah Rujukan Radiologi',
            'HD',
            'Endoskopi',
            'Ekokardiografi',
            "Venti di ICU",
            "Intubasi di ICU",
        ]);
        $writer->addRow($header);

        foreach ($data_pasien as $pasien) {

            // Proses data Nama Kunjungan
            $namaKunjunganData = $pasien['Nama Kunjungan'] ?? null;
            $namaKunjunganFormatted = $this->formatArrayData($namaKunjunganData);

            // Proses data Nama Dokter Kunjungan
            $namaDokterData = $pasien['Nama Dokter Kunjungan'] ?? null;
            $namaDokterFormatted = $this->formatArrayData($namaDokterData);

            $jumlahDokterData = $pasien['Jumlah Kunjungan'] ?? null;
            $jumlahDokterFormatted = $this->formatArrayData($jumlahDokterData);

            $bedData = $pasien['bed'] ?? null;
            $bedFormatted = $this->formatArrayData($bedData);

            $bangsalData = $pasien['bangsal'] ?? null;
            $bangsalFormatted = $this->formatArrayData($bangsalData);

            $lamaData = $pasien['lama'] ?? null;
            $lamaFormatted = $this->formatArrayData($lamaData);

            $dokterlabData = $pasien['dokterlab'] ?? null;
            $dokterlabFormatted = $this->formatArrayData($dokterlabData);

            $jumlahlabData = $pasien['jumlahlab'] ?? null;
            $jumlahlabFormatted = $this->formatArrayData($jumlahlabData);

            $dokterRadData = $pasien['dokterRadio'] ?? null;
            $dokterRadFormatted = $this->formatArrayData($dokterRadData);

            $jumlahRadData = $pasien['jumlahRadio'] ?? null;
            $jumlahRadFormatted = $this->formatArrayData($jumlahRadData);

            $tanggalOperasi = $pasien['tanggal'] ?? null;
            $tanggalOperasiFormatted = $this->formatArrayData($tanggalOperasi);

            $dokter1 = $pasien['dokter1'] ?? null;
            $dokter1Formatted = $this->formatArrayData($dokter1);

            $dokter2 = $pasien['dokter2'] ?? null;
            $dokter2Formatted = $this->formatArrayData($dokter2);

            $dokter3 = $pasien['dokter3'] ?? null;
            $dokter3Formatted = $this->formatArrayData($dokter3);

            $anestesi = $pasien['anestesi'] ?? null;
            $anestesiFormatted = $this->formatArrayData($anestesi);

            // Buat baris data
            $rowData = [
                $pasien['no_rawat'] ?? '',
                $pasien['no_rkm_medis'] ?? '',
                $pasien['sep'] ?? '',
                $pasien['nm_pasien'] ?? '',
                $pasien['dokter_dpjp'] ?? '',
                $pasien['operasiStatus'] ?? '',
                $tanggalOperasiFormatted,
                $dokter1Formatted,
                $dokter2Formatted,
                $dokter3Formatted,
                $anestesiFormatted,
                $namaKunjunganFormatted,
                $namaDokterFormatted,
                $jumlahDokterFormatted,
                $bedFormatted,
                $bangsalFormatted,
                $lamaFormatted,
                $dokterlabFormatted,
                $jumlahlabFormatted,
                $dokterRadFormatted,
                $jumlahRadFormatted,
                $pasien['hd'] ?? '',
                $pasien['endoskopi'] ?? '',
                $pasien['ekokardiografi'] ?? '',
                $pasien['venti in icu'] ?? '',
                $pasien['dokter intubasi'] ?? '',
            ];

            $row = WriterEntityFactory::createRowFromArray($rowData);
            $writer->addRow($row);
        }

        $writer->close();

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    // Fungsi privat untuk memformat array menjadi string
    private function formatArrayData($data)
    {
        if (is_array($data)) {
            // Jika data adalah array numerik, gabungkan menjadi string dengan newline
            return implode("\n", $data);
        }

        return $data === null ? 'Tidak ada data' : (string)$data;
    }

    public function tes(Request $request)
    {
        $no_rawat = $request->input('no_rawat');

        // Query pasien berdasarkan nomor rawat
        $pasien = RegPeriksa::with([
            'pasien',
            'sep',
            'dpjp.dokter',
            'operasi.dokter1',
            'operasi.dokter2',
            'operasi.dokter3',
            'operasi.anestesi',
            'rawatinapdr.dokter',
            'rawatinapdr.kode',
            'kamarinap.kamar.bangsal',
            'periksalab',
            'periksaRadiologi',
            'hemodialisa',
            'rawatInapDrpr.JnsPerawatanInap'
        ])->where('no_rawat', $no_rawat)->first(); // Ambil satu data saja

        if (!$pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => []
            ], 404);
        }

        $operasi = $pasien->operasi;

        $groupedData = [];

        foreach ($operasi as $op) {
            $tanggal = $op->tgl_operasi ?? 'Tanpa Tanggal'; // Jika tidak ada tanggal

            // Pastikan hanya satu entri per tanggal
            if (!isset($groupedData[$tanggal])) {
                $groupedData[$tanggal] = [
                    'tanggal' => $tanggal,
                    'dokter1' => [
                        'kd_dokter' => '-',
                        'nm_dokter' => '-'
                    ],
                    'dokter2' => [
                        'kd_dokter' => '-',
                        'nm_dokter' => '-'
                    ],
                    'dokter3' => [
                        'kd_dokter' => '-',
                        'nm_dokter' => '-'
                    ],
                    'anestesi' => [
                        'kd_dokter' => '-',
                        'nm_dokter' => '-'
                    ]
                ];
            }

            // Isi data dokter jika tersedia
            if ($op->dokter1 && !empty($op->dokter1->kd_dokter) && !empty($op->dokter1->nm_dokter)) {
                $groupedData[$tanggal]['dokter1'] = [
                    'kd_dokter' => $op->dokter1->kd_dokter,
                    'nm_dokter' => $op->dokter1->nm_dokter
                ];
            }

            if ($op->dokter2 && !empty($op->dokter2->kd_dokter) && !empty($op->dokter2->nm_dokter)) {
                $groupedData[$tanggal]['dokter2'] = [
                    'kd_dokter' => $op->dokter2->kd_dokter,
                    'nm_dokter' => $op->dokter2->nm_dokter
                ];
            }

            if ($op->dokter3 && !empty($op->dokter3->kd_dokter) && !empty($op->dokter3->nm_dokter)) {
                $groupedData[$tanggal]['dokter3'] = [
                    'kd_dokter' => $op->dokter3->kd_dokter,
                    'nm_dokter' => $op->dokter3->nm_dokter
                ];
            }

            if ($op->anestesi && !empty($op->anestesi->kd_dokter) && !empty($op->anestesi->nm_dokter)) {
                $groupedData[$tanggal]['anestesi'] = [
                    'kd_dokter' => $op->anestesi->kd_dokter,
                    'nm_dokter' => $op->anestesi->nm_dokter
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => array_values($groupedData) // Konversi ke array numerik
        ], 200);
    }
}