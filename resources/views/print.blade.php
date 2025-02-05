<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Rawat Inap</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .table-container {
                overflow: visible;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>

    <h2>Rincian Rawat Inap</h2>
    {{-- <h3>{{ $data_pasien->tgl_keluar_start }} sampai {{ $data_pasien->tgl_keluar_end }}</h3> --}}
    {{-- <h3>{{ $data_pasien->bangsal->nm_bangsal }}</h3> --}}

    @if (empty($data_pasien))
        <p>Data tidak ditemukan.</p>
    @else
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Rawat</th>
                        <th>No Rekam Medis</th>
                        <th>SEP</th>
                        <th>Nama Pasien</th>
                        <th>Dokter DPJP</th>
                        <th>Dokter Operasi 1</th>
                        <th>Dokter Operasi 2</th>
                        <th>Dokter Operasi 3</th>
                        <th>Dokter Anestesi</th>
                        <th>Operasi</th>
                        <th>Nama Dokter Kunjungan</th>
                        <th>Kamar</th>
                        <th>Lab</th>
                        <th>Radiologi</th>
                        <th>HD</th>
                        <th>Endoskopi</th>
                        <th>Ekokardiografi</th>
                        <th>Venti di ICU</th>
                        <th>Intubasi di ICU</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_pasien as $index => $pasien)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pasien['no_rawat'] }}</td>
                            <td>{{ $pasien['no_rkm_medis'] }}</td>
                            <td>{{ $pasien['sep'] }}</td>
                            <td>{{ $pasien['nm_pasien'] }}</td>
                            <td>{{ $pasien['dokter_dpjp'] }}</td>
                            <td>{{ $pasien['dokter1'] }}</td>
                            <td>{{ $pasien['dokter2'] }}</td>
                            <td>{{ $pasien['dokter3'] }}</td>
                            <td>{{ $pasien['anestesi'] }}</td>
                            <td>{{ $pasien['operasi'] }}</td>
                            <td>
                                <table>
                                    <tr>
                                        <th>Nama Dokter</th>
                                        <th>Jenis Kunjungan</th>
                                        <th>Jumlah Kunjungan</th>
                                    </tr>
                                    @foreach ($pasien['kunjungan'] as $kunjungan)
                                        <tr>
                                            <td>{{ $kunjungan['nama_dokter'] }}</td>
                                            <td>{{ $kunjungan['nama'] }}</td>
                                            <td>{{ $kunjungan['jumlah_kode'] }} kali</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td>
                                <table>
                                    <tr>
                                        <th>Bangsal</th>
                                        <th>Bed</th>
                                        <th>Lama Rawat</th>
                                    </tr>
                                    @foreach ($pasien['kamar'] as $kamar)
                                        <tr>
                                            <td>{{ $kamar['bangsal'] }}</td>
                                            <td>{{ $kamar['bed'] }}</td>
                                            <td>{{ $kamar['lama'] }} hari</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td>
                                <table>
                                    <tr>
                                        <th>No</th>
                                        <th>Dokter Perujuk</th>
                                        <th>Jumlah</th>
                                    </tr>
                                    @foreach ($pasien['lab'] as $index => $lab)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $lab['Dokter perujuk'] }}</td>
                                            <td>{{ $lab['Jumlah rujukan'] }} kali</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td>
                                <table>
                                    <tr>
                                        <th>No</th>
                                        <th>Dokter Perujuk</th>
                                        <th>Jumlah</th>
                                    </tr>
                                    @foreach ($pasien['radiologi'] as $index => $radiologi)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $radiologi['Dokter perujuk'] }}</td>
                                            <td>{{ $radiologi['Jumlah rujukan'] }} kali</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td>{{ $pasien['hd'] }}</td>
                            <td>{{ $pasien['endoskopi'] }}</td>
                            <td>{{ $pasien['ekokardiografi'] }}</td>
                            <td>{{ $pasien['venti in icu'] }}</td>
                            <td>
                                <table>
                                    <tr>
                                        <th>Dokter Perujuk</th>
                                    </tr>
                                    @foreach ($pasien['venti in intubasi']['dokter'] as $dokter)
                                        <tr>
                                            <td>{{ $dokter['nama_dokter'] }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>

</html>
