<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Siswa</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
            color: #111;
            margin: 16px;
        }

        .header-table,
        .info-table,
        .nilai-table,
        .ekskul-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td,
        .info-table td {
            vertical-align: middle;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .school-name {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school-sub {
            text-align: center;
            font-size: 11px;
            line-height: 1.45;
        }

        .line {
            border-top: 2px solid #000;
            margin: 10px 0 12px 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .info-table {
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 3px 4px;
            font-size: 11px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .nilai-table,
        .ekskul-table {
            margin-top: 6px;
            margin-bottom: 14px;
        }

        .nilai-table th,
        .nilai-table td,
        .ekskul-table th,
        .ekskul-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .nilai-table th,
        .ekskul-table th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .nilai-table thead th {
            line-height: 1.2;
        }

        .nilai-table tfoot th,
        .nilai-table tfoot td {
            font-weight: bold;
            background: #fff;
        }

        .text-left {
            text-align: left !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .validity-note {
            margin-top: 10px;
            padding: 6px 8px;
            border: 1px solid #000;
            font-size: 10px;
            line-height: 1.4;
        }

        .signature-table {
            margin-top: 18px;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 11px;
        }

        .signature-box {
            width: 230px;
            float: right;
            text-align: center;
        }

        .ttd-area {
            height: 64px;
            margin-top: 4px;
            margin-bottom: 2px;
            text-align: center;
        }

        .ttd-img {
            max-height: 64px;
            max-width: 180px;
            object-fit: contain;
        }

        .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
        }

        .nip-ttd {
            margin-top: 2px;
            font-size: 10px;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 10px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    @php
        $tanggalCetakLabel = isset($tanggalCetak)
            ? \Illuminate\Support\Carbon::parse($tanggalCetak)->translatedFormat('d F Y')
            : now()->translatedFormat('d F Y');

        $waliNama = $waliKelas->nama ?? 'Wali Kelas';

        $waliIdentitas = '-';

        if (!empty($waliKelas->nip)) {
            $waliIdentitas = 'NIP. ' . $waliKelas->nip;
        } elseif (!empty($waliKelas->nuptk)) {
            $waliIdentitas = 'NUPTK. ' . $waliKelas->nuptk;
        }

        $namaSiswa = $siswa->nama ?? '-';
        $nisSiswa = $siswa->nis ?? '-';
        $nisnSiswa = $siswa->nisn ?? '-';
    @endphp

    <table class="header-table">
        <tr>
            <td width="14%">
                @if(!empty($logoPath) && file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo" alt="Logo Sekolah">
                @endif
            </td>
            <td width="72%">
                <div class="school-name">{{ $namaSekolah }}</div>
                <div class="school-sub">
                    {{ $alamatSekolah }}<br>
                    Sistem Informasi Akademik SMA Negeri 2 Temanggung
                </div>
            </td>
            <td width="14%"></td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="title">
        LAPORAN NILAI SEMESTER {{ strtoupper($semester) }}<br>
        TAHUN AJARAN {{ $tahunAjaran }}
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Nama</td>
            <td width="2%">:</td>
            <td width="33%">{{ $namaSiswa }}</td>

            <td width="15%">Kelas</td>
            <td width="2%">:</td>
            <td width="33%">{{ $kelas }}</td>
        </tr>
        <tr>
            <td>NIS</td>
            <td>:</td>
            <td>{{ $nisSiswa }}</td>

            <td>Semester</td>
            <td>:</td>
            <td>{{ $semester }}</td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td>{{ $nisnSiswa }}</td>

            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>{{ $tahunAjaran }}</td>
        </tr>
    </table>

    <div class="section-title">A. Nilai Mata Pelajaran</div>

    <table class="nilai-table">
        <thead>
            <tr>
                <th rowspan="3" style="width:4%;">No</th>
                <th rowspan="3" style="width:14%;">Mata Pelajaran</th>

                <th colspan="16">FORMATIF</th>
                <th colspan="4">SUMATIF LINGKUP MATERI</th>
                <th rowspan="3" style="width:7%;">NILAI AKHIR</th>
                <th rowspan="3" style="width:7%;">PREDIKAT</th>
            </tr>
            <tr>
                <th colspan="4">Lingkup Materi 1</th>
                <th colspan="4">Lingkup Materi 2</th>
                <th colspan="4">Lingkup Materi 3</th>
                <th colspan="4">Lingkup Materi 4</th>

                <th rowspan="2">LM1</th>
                <th rowspan="2">LM2</th>
                <th rowspan="2">LM3</th>
                <th rowspan="2">LM4</th>
            </tr>
            <tr>
                <th>TP1</th>
                <th>TP2</th>
                <th>TP3</th>
                <th>TP4</th>

                <th>TP1</th>
                <th>TP2</th>
                <th>TP3</th>
                <th>TP4</th>

                <th>TP1</th>
                <th>TP2</th>
                <th>TP3</th>
                <th>TP4</th>

                <th>TP1</th>
                <th>TP2</th>
                <th>TP3</th>
                <th>TP4</th>
            </tr>
        </thead>

        <tbody>
            @forelse($nilai as $row)
                @php
                    $namaMapel = $row->mapel ?? $row->nama_mapel ?? $row->mata_pelajaran ?? '-';
                    $predikat = ($row->status ?? null) === 'tuntas' ? 'Tuntas' : 'Tidak Tuntas';
                @endphp

                <tr>
                    <td>{{ $row->no }}</td>
                    <td class="text-left">{{ $namaMapel }}</td>

                    <td>{{ $row->lm1_tp1 !== null ? number_format($row->lm1_tp1, 2) : '-' }}</td>
                    <td>{{ $row->lm1_tp2 !== null ? number_format($row->lm1_tp2, 2) : '-' }}</td>
                    <td>{{ $row->lm1_tp3 !== null ? number_format($row->lm1_tp3, 2) : '-' }}</td>
                    <td>{{ $row->lm1_tp4 !== null ? number_format($row->lm1_tp4, 2) : '-' }}</td>

                    <td>{{ $row->lm2_tp1 !== null ? number_format($row->lm2_tp1, 2) : '-' }}</td>
                    <td>{{ $row->lm2_tp2 !== null ? number_format($row->lm2_tp2, 2) : '-' }}</td>
                    <td>{{ $row->lm2_tp3 !== null ? number_format($row->lm2_tp3, 2) : '-' }}</td>
                    <td>{{ $row->lm2_tp4 !== null ? number_format($row->lm2_tp4, 2) : '-' }}</td>

                    <td>{{ $row->lm3_tp1 !== null ? number_format($row->lm3_tp1, 2) : '-' }}</td>
                    <td>{{ $row->lm3_tp2 !== null ? number_format($row->lm3_tp2, 2) : '-' }}</td>
                    <td>{{ $row->lm3_tp3 !== null ? number_format($row->lm3_tp3, 2) : '-' }}</td>
                    <td>{{ $row->lm3_tp4 !== null ? number_format($row->lm3_tp4, 2) : '-' }}</td>

                    <td>{{ $row->lm4_tp1 !== null ? number_format($row->lm4_tp1, 2) : '-' }}</td>
                    <td>{{ $row->lm4_tp2 !== null ? number_format($row->lm4_tp2, 2) : '-' }}</td>
                    <td>{{ $row->lm4_tp3 !== null ? number_format($row->lm4_tp3, 2) : '-' }}</td>
                    <td>{{ $row->lm4_tp4 !== null ? number_format($row->lm4_tp4, 2) : '-' }}</td>

                    <td>{{ $row->lm1_nilai !== null ? number_format($row->lm1_nilai, 2) : '-' }}</td>
                    <td>{{ $row->lm2_nilai !== null ? number_format($row->lm2_nilai, 2) : '-' }}</td>
                    <td>{{ $row->lm3_nilai !== null ? number_format($row->lm3_nilai, 2) : '-' }}</td>
                    <td>{{ $row->lm4_nilai !== null ? number_format($row->lm4_nilai, 2) : '-' }}</td>

                    <td>{{ $row->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '-' }}</td>
                    <td>{{ $predikat }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="24">Tidak ada data nilai.</td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <th colspan="22" class="text-right">Rata-rata Semester</th>
                <th>{{ $rataSemester !== null ? number_format($rataSemester, 2) : '-' }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">B. Nilai Ekstrakurikuler</div>

    <table class="ekskul-table">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th>Nama Ekstrakurikuler</th>
                <th style="width:12%;">Nilai</th>
                <th style="width:12%;">Predikat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ekskul as $i => $eks)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $eks->nama_ekskul }}</td>
                    <td>{{ $eks->nilai_akhir ?? '-' }}</td>
                    <td>{{ $eks->predikat ?? '-' }}</td>
                    <td class="text-left">{{ $eks->deskripsi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tidak ada data ekstrakurikuler.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="validity-note">
        Dokumen ini merupakan laporan nilai yang dihasilkan melalui Sistem Informasi Akademik
        {{ $namaSekolah }}. Nilai pada dokumen ini merupakan nilai yang telah difinalisasi
        oleh guru mata pelajaran dan diketahui oleh wali kelas.
    </div>

    <table class="signature-table">
        <tr>
            <td width="65%"></td>
            <td width="35%">
                <div class="signature-box">
                    Temanggung, {{ $tanggalCetakLabel }}<br>
                    Mengetahui,<br>
                    Wali Kelas {{ $kelas }}

                    <div class="ttd-area">
                        @if(!empty($ttdWaliKelasPath) && file_exists($ttdWaliKelasPath))
                            <img src="{{ $ttdWaliKelasPath }}" class="ttd-img" alt="Tanda tangan wali kelas">
                        @endif
                    </div>

                    <div class="nama-ttd">{{ $waliNama }}</div>
                    <div class="nip-ttd">{{ $waliIdentitas }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dicetak melalui Sistem Informasi Akademik {{ $namaSekolah }} pada {{ $tanggalCetakLabel }}.
    </div>
</body>
</html>