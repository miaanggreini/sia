{{-- resources/views/siswa/ekskul/show.blade.php --}}
@extends('layouts.siswa')

@php
    $ekskul = $ekskul ?? ($anggota->ekskul ?? null);

    $tahunDipilih = $tahunDipilih ?? ($anggota->tahunAjaran ?? null);
    $tahunAjaranId = $tahunAjaranId ?? ($anggota->tahun_ajaran_id ?? request('tahun_ajaran_id'));

    $riwayatAnggota = $riwayatAnggota ?? collect([$anggota]);

    $presensiRows = collect();

    if (isset($presensiList)) {
        $presensiRows = collect($presensiList);
    } elseif (isset($rekapPresensi)) {
        $presensiRows = collect($rekapPresensi);
    } elseif (isset($rekapPerTanggal)) {
        $presensiRows = collect($rekapPerTanggal);
    }

    $rekapStatus = [];

    if (isset($summary)) {
        $rekapStatus = $summary;
    } elseif (isset($rekapStatusPerSiswa)) {
        $rekapStatus = $rekapStatusPerSiswa;
    }

    $defaultTab = ($tab ?? request('tab')) === 'nilai' ? 'nilai' : 'presensi';

    $namaTahunDipilih = $tahunDipilih->nama_tahun
        ?? $tahunDipilih->label
        ?? $tahunDipilih->nama
        ?? '-';

    $tanggalGabung = $anggota->tanggal_gabung ?? null;
    $tanggalGabungFormatted = $tanggalGabung
        ? \Carbon\Carbon::parse($tanggalGabung)->locale('id')->translatedFormat('d M Y')
        : '-';

    $isTahunAktif = $tahunAktif && (int) $tahunAjaranId === (int) $tahunAktif->id;

    $totalPresensi = $rekapStatus['total'] ?? $presensiRows->count();
    $totalHadir = $rekapStatus['hadir'] ?? 0;
    $totalIzin = $rekapStatus['izin'] ?? 0;
    $totalSakit = $rekapStatus['sakit'] ?? 0;
    $totalAlfa = $rekapStatus['alfa'] ?? 0;

    $nilaiRow = null;

    if (isset($nilaiAkhirEkskul)) {
        $nilaiRow = $nilaiAkhirEkskul;
    } elseif (isset($nilaiEkskul)) {
        $nilaiRow = $nilaiEkskul;
    } elseif (isset($penilaianEkskul)) {
        $nilaiRow = $penilaianEkskul;
    } elseif (isset($nilaiAkhir)) {
        $nilaiRow = $nilaiAkhir;
    } elseif (isset($anggota) && isset($anggota->nilaiAkhir)) {
        $nilaiRow = $anggota->nilaiAkhir;
    } elseif (isset($anggota) && isset($anggota->nilai)) {
        $nilaiRow = $anggota->nilai;
    }

    if (is_numeric($nilaiRow)) {
        $nilaiAkhirValue = $nilaiRow;
        $predikatValue = null;
        $deskripsiValue = null;
    } else {
        $nilaiAkhirValue = data_get($nilaiRow, 'nilai_akhir')
            ?? data_get($nilaiRow, 'nilai')
            ?? data_get($nilaiRow, 'skor')
            ?? data_get($anggota, 'nilai_akhir');

        $predikatValue = data_get($nilaiRow, 'predikat')
            ?? data_get($anggota, 'predikat');

        $deskripsiValue = data_get($nilaiRow, 'deskripsi')
            ?? data_get($nilaiRow, 'keterangan')
            ?? data_get($anggota, 'deskripsi')
            ?? data_get($anggota, 'keterangan');
    }

    if (!$predikatValue && $nilaiAkhirValue !== null) {
        if ($nilaiAkhirValue >= 90) {
            $predikatValue = 'A';
        } elseif ($nilaiAkhirValue >= 80) {
            $predikatValue = 'B';
        } elseif ($nilaiAkhirValue >= 70) {
            $predikatValue = 'C';
        } else {
            $predikatValue = 'D';
        }
    }

    $formatJam = function ($value) {
        if (!$value) {
            return '–';
        }

        return \Illuminate\Support\Str::substr($value, 0, 5);
    };

    $statusClass = function ($status) {
        $status = strtolower((string) $status);

        return match ($status) {
            'hadir', 'h' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'izin', 'i' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sakit', 's' => 'bg-amber-50 text-amber-700 border-amber-200',
            'alfa', 'alpha', 'a', 'tidak hadir' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    };

    $formatStatus = function ($status) {
        $status = strtolower((string) $status);

        return match ($status) {
            'h' => 'Hadir',
            'i' => 'Izin',
            's' => 'Sakit',
            'a' => 'Alfa',
            default => ucfirst((string) $status),
        };
    };

    $hasKeterangan = $presensiRows->contains(function ($row) {
        $keterangan = data_get($row, 'keterangan') ?? data_get($row, 'catatan');
        return filled($keterangan);
    });
@endphp

@section('title', 'Ekskul ' . ($ekskul->nama ?? ''))

@section('content')
<div x-data="{ tab: '{{ $defaultTab }}' }" class="space-y-6">

 {{-- HEADER --}}
<div class="rounded-2xl border bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        {{-- KIRI --}}
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Ekskul {{ $ekskul->nama ?? '-' }}
                </h1>

</div>

            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                    Pembina:
                    <strong class="ml-1 text-gray-900">{{ $ekskul->pembina->nama ?? '-' }}</strong>
                </span>

                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">
                    Jadwal:
                    <strong class="ml-1">
                        {{ $ekskul->hari ?? '-' }},
                        {{ $formatJam($ekskul->jam_mulai ?? null) }}–{{ $formatJam($ekskul->jam_selesai ?? null) }}
                    </strong>
                </span>

                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                    Lokasi:
                    <strong class="ml-1 text-gray-900">{{ $ekskul->lokasi ?? '-' }}</strong>
                </span>
            </div>

            {{-- FILTER TAHUN AJARAN PINDAH KE KIRI --}}
            @if(($riwayatAnggota ?? collect())->count() > 1)
                <div class="mt-4 max-w-xs">
                    <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Riwayat Tahun Ajaran
                    </label>

                    <form method="GET"
                          action="{{ route('siswa.ekskul.show', $ekskul->id) }}"
                          class="w-full">
                        <input type="hidden" name="tab" :value="tab">

                        <select id="tahun_ajaran_id"
                                name="tahun_ajaran_id"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border-gray-300 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($riwayatAnggota as $riwayat)
                                @php
                                    $tahunRiwayat = $riwayat->tahunAjaran;
                                    $labelRiwayat = $tahunRiwayat->nama_tahun
                                        ?? $tahunRiwayat->label
                                        ?? $tahunRiwayat->nama
                                        ?? '-';
                                @endphp

                                <option value="{{ $riwayat->tahun_ajaran_id }}"
                                    @selected((int)($tahunAjaranId ?? 0) === (int)$riwayat->tahun_ajaran_id)>
                                    {{ $labelRiwayat }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        {{-- KANAN --}}
        <div class="w-full lg:w-[340px]">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-gray-50 px-4 py-3 text-center lg:text-right">
                    <div class="text-xs text-gray-500">Tahun ajaran</div>
                    <div class="font-semibold text-gray-900">{{ $namaTahunDipilih }}</div>
                </div>

                <div class="rounded-xl bg-gray-50 px-4 py-3 text-center lg:text-right">
                    <div class="text-xs text-gray-500">Bergabung</div>
                    <div class="font-semibold text-gray-900">{{ $tanggalGabungFormatted }}</div>
                </div>
            </div>

           
        </div>
    </div>
</div>

    {{-- CARD KONTEN + TAB MENYATU --}}
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="border-b px-5 pt-4">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="tab = 'presensi'"
                    :class="tab === 'presensi'
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100'"
                    class="rounded-t-xl px-4 py-2.5 text-sm font-medium transition"
                >
                    Presensi
                </button>

                <button
                    type="button"
                    @click="tab = 'nilai'"
                    :class="tab === 'nilai'
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100'"
                    class="rounded-t-xl px-4 py-2.5 text-sm font-medium transition"
                >
                    Nilai Akhir
                </button>
            </div>
        </div>

        {{-- TAB PRESENSI --}}
        <div x-show="tab === 'presensi'" x-cloak>
            <div class="border-b px-5 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Riwayat Presensi
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Daftar kehadiran ekskul pada tahun ajaran {{ $namaTahunDipilih }}.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                            Total {{ $totalPresensi }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
                            Hadir {{ $totalHadir }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700">
                            Izin {{ $totalIzin }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-700">
                            Sakit {{ $totalSakit }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 font-semibold text-red-700">
                            Alfa {{ $totalAlfa }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="w-16 px-5 py-3">No</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="w-40 px-5 py-3">Status</th>
                            @if($hasKeterangan)
                                <th class="px-5 py-3">Keterangan</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($presensiRows as $i => $row)
                            @php
                                $tanggal = data_get($row, 'tanggal')
                                    ?? data_get($row, 'created_at')
                                    ?? data_get($row, 'tgl')
                                    ?? null;

                                $status = data_get($row, 'status')
                                    ?? data_get($row, 'kehadiran')
                                    ?? '-';

                                $keterangan = data_get($row, 'keterangan')
                                    ?? data_get($row, 'catatan')
                                    ?? '-';
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 text-gray-600">{{ $i + 1 }}</td>

                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </div>
                                    @if($tanggal)
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass($status) }}">
                                        {{ $formatStatus($status) }}
                                    </span>
                                </td>

                                @if($hasKeterangan)
                                    <td class="px-5 py-4 text-gray-600">
                                        {{ filled($keterangan) ? $keterangan : '-' }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hasKeterangan ? 4 : 3 }}" class="px-5 py-10 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="text-sm font-semibold text-gray-800">
                                            Belum ada data presensi
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Data presensi ekskul untuk tahun ajaran ini belum tersedia.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB NILAI --}}
        <div x-show="tab === 'nilai'" x-cloak>
            <div class="border-b px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    Rekap Nilai Akhir Ekskul
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Nilai akhir diberikan oleh pembina ekskul pada tahun ajaran {{ $namaTahunDipilih }}.
                </p>
            </div>

            @if($nilaiAkhirValue !== null || $predikatValue || $deskripsiValue)
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border bg-gray-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Nilai Akhir
                            </div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $nilaiAkhirValue !== null ? number_format((float) $nilaiAkhirValue, 0) : '-' }}
                            </div>
                        </div>

                        <div class="rounded-xl border bg-gray-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Predikat
                            </div>
                            <div class="mt-3">
                                @if($predikatValue)
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                        {{ $predikatValue }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-xl border bg-gray-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Deskripsi
                            </div>
                            <p class="mt-2 text-sm leading-6 text-gray-700">
                                {{ $deskripsiValue ?: '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-5 py-10 text-center">
                    <div class="text-sm font-semibold text-gray-800">
                        Nilai akhir belum tersedia
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Pembina ekskul belum menginput nilai akhir untuk tahun ajaran ini.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection