<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Rawat Inap</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .table-container {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling untuk iOS */
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 10px;
            max-width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            min-width: 1000px;
            /* Pastikan tabel bisa di-scroll */
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            white-space: nowrap;
            /* Hindari teks terpotong */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        form {
            margin-bottom: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            margin-right: 10px;
            font-weight: bold;
        }

        select,
        input[type="date"],
        button {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        button.danger {
            background-color: #f44336;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.danger:hover {
            background-color: #d32f2f;
        }

        hr {
            margin: 5px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }

        /* Responsive Styling */
        @media screen and (max-width: 768px) {
            .table-container {
                padding: 5px;
            }

            table {
                min-width: 800px;
                /* Memastikan tabel masih bisa di-scroll */
            }

            form {
                display: flex;
                flex-direction: column;
            }

            select,
            input[type="date"],
            button {
                width: 100%;
                margin-bottom: 10px;
            }
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

    <form action="{{ route('logout') }}" method="POST" class="nav-link mx-3" style="text-align: center;">
        @csrf
        <h2>Rincian Rawat Inap</h2>
        <button type="submit" class="danger" style="margin-top: 10px;">Logout</button>
    </form>

    <form action="{{ route('rincian.rawat.inap') }}" method="GET">
        <label for="tgl_keluar_start">Tanggal Keluar Mulai:</label>
        <input type="date" id="tgl_keluar_start" name="tgl_keluar_start" value="{{ $tgl_keluar_start }}">

        <label for="tgl_keluar_end">Tanggal Keluar Akhir:</label>
        <input type="date" id="tgl_keluar_end" name="tgl_keluar_end" value="{{ $tgl_keluar_end }}">

        <label for="kode_bangsal">Kode Bangsal:</label>

        <select id="kode_bangsal" name="kode_bangsal" disabled>
            <option value="">-- Pilih Kode Bangsal --</option>
            @foreach ($bangsalList as $bangsal)
                @if ($bangsal->kd_bangsal == session('kode_bangsal'))
                    <option value="{{ $bangsal->kd_bangsal }}" selected>
                        {{ $bangsal->nm_bangsal }}
                    </option>
                @endif
            @endforeach
        </select>

        <button type="submit">Tampilkan</button>

        <a href="{{ url('rincian-rawat-inap/print') }}">
            <button type="button" class="danger">PDF</button>
        </a>

        <a href="{{ route('rincian.rawatinap.export') }}">
            <button type="button">Import Excel</button>
        </a>
    </form>

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
                        <th>Operasi</th>
                        <th>Petugas Operasi</th>
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
                            <td>{{ $pasien['operasiStatus'] }}</td>
                            <td>
                                <table>
                                    <tr>
                                        <th>Tanggal Operasi</th>
                                        <th>Dokter 1</th>
                                        <th>Dokter 2</th>
                                        <th>Dokter 3</th>
                                        <th>Anestesi</th>
                                    </tr>
                                    @foreach ($pasien['operasi'] as $operasi)
                                        <tr>
                                            <td>{{ $operasi['Tanggal Operasi'] }}</td>
                                            <td>{{ $operasi['Dokter 1'] }}</td>
                                            <td>{{ $operasi['Dokter 2'] }}</td>
                                            <td>{{ $operasi['Dokter 3'] }} </td>
                                            <td>{{ $operasi['Anestesi'] }} </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
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
                                    @foreach ($pasien['kamar'] as $kamars)
                                        <tr>
                                            <td>{{ $kamars['bangsal'] }}</td>
                                            <td>{{ $kamars['bed'] }}</td>
                                            <td>{{ $kamars['lama'] }} hari</td>
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
                                    @foreach ($pasien['lab'] as $index => $labs)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $labs['Dokter perujuk'] }} </td>
                                            <td>{{ $labs['Jumlah rujukan'] }} kali </td>
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
                                    @foreach ($pasien['radiologi'] as $index => $radiologis)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $radiologis['Dokter perujuk'] }} </td>
                                            <td>{{ $radiologis['Jumlah rujukan'] }} kali </td>
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
                                        <th>No</th>
                                        <th>Dokter Perujuk</th>
                                    </tr>
                                    <tr>
                                        <td>{{ $pasien['intubasi in icu']['status'] }}</td>
                                        <td>
                                            <table>
                                                @foreach ($pasien['intubasi in icu']['dokter'] as $index => $dokter)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $dokter['nama_dokter'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
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
