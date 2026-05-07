@php
    $semesterValue = strtolower($semester ?? 'ganjil');
    $semesterLabel = ucfirst($semesterValue);

    $tahunAjaranLabel = is_object($taAktif ?? null)
        ? ($taAktif->nama_tahun ?? '-')
        : ($taAktif ?? '-');

    $namaSiswa = $siswa->nama ?? '-';
    $nis = $siswa->nis ?? '-';
    $nisn = $siswa->nisn ?? '-';
    $kelas = $rombel->nama_rombel ?? '-';

    $fmt = function ($value) {
        return $value !== null ? number_format((float) $value, 2, '.', '') : '-';
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai {{ $namaSiswa }}</title>

    <style>
        @page {
            margin: 18px 26px 22px 26px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            color: #000;
        }

        .kop {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-logo {
            width: 90px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-box {
            width: 58px;
            height: 58px;
            margin: 0 auto;
            border: 1px solid #999;
            border-radius: 4px;
            text-align: center;
            line-height: 58px;
            font-size: 9px;
            color: #555;
        }

        .kop-title {
            text-align: center;
            vertical-align: middle;
        }

        .kop-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-title p {
            margin: 2px 0;
            font-size: 11px;
        }

        .title {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 18px;
        }

        .title h2 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title h3 {
            margin: 4px 0 0 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .identity-wrapper {
            width: 100%;
            margin-bottom: 14px;
        }

        .identity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .identity-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 11px;
        }

        .identity-label {
            width: 95px;
        }

        .identity-separator {
            width: 8px;
            text-align: center;
        }

        .identity-value {
            width: 230px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 12px 0 6px 0;
        }

        table.nilai {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.nilai th,
        table.nilai td {
            border: 1px solid #000;
            padding: 4px 3px;
            font-size: 9px;
            vertical-align: middle;
        }

        table.nilai th {
            text-align: center;
            font-weight: bold;
            background: #f2f2f2;
        }

        table.nilai td.center {
            text-align: center;
        }

        table.nilai td.right {
            text-align: right;
        }

        table.nilai td.mapel {
            text-align: left;
            padding-left: 5px;
        }

        table.nilai td.bold {
            font-weight: bold;
        }

        table.ekskul {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.ekskul th,
        table.ekskul td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 10px;
            vertical-align: middle;
        }

        table.ekskul th {
            text-align: center;
            font-weight: bold;
            background: #f2f2f2;
        }

        .empty {
            text-align: center;
            font-style: italic;
            color: #555;
        }

        .footer-note {
            margin-top: 22px;
            font-size: 10px;
        }

        .small-note {
            margin-top: 4px;
            font-size: 9px;
            font-style: italic;
        }

        .no-border {
            border: none !important;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="kop">
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <div class="logo-box">
                        LOGO
                    </div>
                </td>
                <td class="kop-title">
                    <h1>SMA Negeri 2 Temanggung</h1>
                    <p>Jalan Pahlawan, Giyanti, Temanggung, Jawa Tengah</p>
                    <p>Sistem Informasi Akademik SMA Negeri 2 Temanggung</p>
                </td>
                <td style="width: 90px;"></td>
            </tr>
        </table>
    </div>

    {{-- JUDUL --}}
    <div class="title">
        <h2>Laporan Hasil Penilaian Siswa</h2>
        <h3>Semester {{ $semesterLabel }} Tahun Ajaran {{ $tahunAjaranLabel }}</h3>
    </div>

    {{-- IDENTITAS --}}
    <div class="identity-wrapper">
        <table class="identity-table">
            <tr>
                <td class="identity-label">Nama</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $namaSiswa }}</td>

                <td class="identity-label">Kelas</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $kelas }}</td>
            </tr>
            <tr>
                <td class="identity-label">NIS</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $nis }}</td>

                <td class="identity-label">Semester</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $semesterLabel }}</td>
            </tr>
            <tr>
                <td class="identity-label">NISN</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $nisn }}</td>

                <td class="identity-label">Tahun Ajaran</td>
                <td class="identity-separator">:</td>
                <td class="identity-value">{{ $tahunAjaranLabel }}</td>
            </tr>
        </table>
    </div>

    {{-- A. NILAI MATA PELAJARAN --}}
    <div class="section-title">A. Nilai Mata Pelajaran</div>

    <table class="nilai">
        <thead>
            <tr>
                <th rowspan="3" style="width: 28px;">No</th>
                <th rowspan="3" style="width: 118px;">Mata Pelajaran</th>

                <th colspan="16">Formatif</th>
                <th colspan="4">Sumatif Lingkup Materi</th>

                <th rowspan="3" style="width: 48px;">Nilai Akhir</th>
                <th rowspan="3" style="width: 48px;">Predikat</th>
                <th rowspan="3" style="width: 58px;">Status</th>
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
            @forelse($rows as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="mapel">{{ $row->mapel_nama ?? '-' }}</td>

                    <td class="center">{{ $fmt($row->lm1_tp1 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm1_tp2 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm1_tp3 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm1_tp4 ?? null) }}</td>

                    <td class="center">{{ $fmt($row->lm2_tp1 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm2_tp2 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm2_tp3 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm2_tp4 ?? null) }}</td>

                    <td class="center">{{ $fmt($row->lm3_tp1 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm3_tp2 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm3_tp3 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm3_tp4 ?? null) }}</td>

                    <td class="center">{{ $fmt($row->lm4_tp1 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm4_tp2 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm4_tp3 ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm4_tp4 ?? null) }}</td>

                    <td class="center">{{ $fmt($row->lm1_nilai ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm2_nilai ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm3_nilai ?? null) }}</td>
                    <td class="center">{{ $fmt($row->lm4_nilai ?? null) }}</td>

                    <td class="center">{{ $fmt($row->nilai_akhir ?? null) }}</td>
                    <td class="center">{{ $row->predikat ?? '-' }}</td>
                    <td class="center">
                        {{ !empty($row->status) ? ucfirst(str_replace('_', ' ', $row->status)) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="25" class="empty">
                        Belum ada data penilaian mata pelajaran untuk semester {{ $semesterLabel }}.
                    </td>
                </tr>
            @endforelse

            @if(!empty($rows) && collect($rows)->isNotEmpty())
                <tr>
                    <td colspan="22" class="right bold">
                        Rata-rata Semester
                    </td>
                    <td class="center bold">
                        {{ $rataRataSemester !== null ? number_format((float) $rataRataSemester, 2, '.', '') : '-' }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- B. NILAI EKSTRAKURIKULER --}}
    <div class="section-title">B. Nilai Ekstrakurikuler</div>

    <table class="ekskul">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Ekstrakurikuler</th>
                <th style="width: 80px;">Nilai</th>
                <th style="width: 80px;">Predikat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ekskulRows as $index => $ekskul)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $ekskul->nama_ekskul ?? '-' }}</td>
                    <td class="center">
                        {{ $ekskul->nilai_akhir !== null ? number_format((float) $ekskul->nilai_akhir, 2, '.', '') : '-' }}
                    </td>
                    <td class="center">{{ $ekskul->predikat ?? '-' }}</td>
                    <td>{{ $ekskul->deskripsi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        Belum ada nilai ekstrakurikuler untuk siswa ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Dicetak melalui Sistem Informasi Akademik SMA Negeri 2 Temanggung.
    </div>

    <div class="small-note">
        Dokumen ini merupakan rekap hasil penilaian dari Sistem Informasi Akademik dan bukan rapor resmi sekolah.
    </div>

</body>
</html>