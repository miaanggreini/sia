@extends('layouts.kepsek')

@section('title','Monitoring Presensi')

@section('content')
@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    [$year, $month] = explode('-', $bulan);

    $labelBulan = Carbon::createFromDate($year, $month, 1)
        ->locale('id')
        ->translatedFormat('F Y');

    $tahunLabel = $tahunDipilih->nama_tahun
        ?? $tahunDipilih->nama
        ?? $tahunDipilih->tahun
        ?? $tahunDipilih->label
        ?? '-';

    $tahunAktifId = $taAktif->id ?? null;
    $isTahunAktif = $tahunAktifId && (int) $tahunAjaranId === (int) $tahunAktifId;

    $statusBg = function ($persen) {
        if ($persen >= 90) {
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        }

        if ($persen >= 80) {
            return 'bg-amber-100 text-amber-700 border-amber-200';
        }

        return 'bg-rose-100 text-rose-700 border-rose-200';
    };

    /*
     * Supaya tampilan kepsek sama dengan admin:
     * - Jika H + A = 0, tampilkan 100% agar tidak dianggap bermasalah.
     * - Sorting juga menggunakan persen tampilan ini.
     */
    $rombelRows = collect($rombelSummaries ?? [])
        ->map(function ($row) {
            $h = (int) ($row['stats']['H'] ?? 0);
            $a = (int) ($row['stats']['A'] ?? 0);
            $totalHadirAlfa = $h + $a;

            $row['persen_tampil'] = $totalHadirAlfa > 0
                ? (float) ($row['persen'] ?? 0)
                : 100.0;

            return $row;
        })
        ->sortBy([
            ['persen_tampil', 'asc'],
            ['perlu_perhatian', 'desc'],
            ['nama_rombel', 'asc'],
        ])
        ->values();

    $routeIndex = route('kepala_sekolah.monitor.presensi.index');
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Monitoring Presensi
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Rekap monitoring presensi siswa bulan
                <span class="font-semibold text-blue-700">{{ $labelBulan }}</span>
                • Tahun ajaran:
                <span class="font-semibold text-blue-700">
                    {{ $tahunLabel }}
                    @if($isTahunAktif)
                        — Aktif
                    @else
                        — Histori
                    @endif
                </span>
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs text-gray-500 shadow-sm">
            Periode:
            {{ $start->locale('id')->translatedFormat('d M Y') }}
            s.d.
            {{ $end->locale('id')->translatedFormat('d M Y') }}
        </div>
    </div>

    {{-- Filter --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form id="filterPresensiForm" method="GET" action="{{ $routeIndex }}">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Tahun Ajaran
                    </label>

                    <select name="tahun_ajaran_id"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($daftarTahunAjaran as $ta)
                            @php
                                $labelTa = $ta->nama_tahun
                                    ?? $ta->nama
                                    ?? $ta->tahun
                                    ?? $ta->label
                                    ?? '-';

                                $isAktifOption = $taAktif && (int) $ta->id === (int) $taAktif->id;
                            @endphp

                            <option value="{{ $ta->id }}" @selected((int) $tahunAjaranId === (int) $ta->id)>
                                {{ $labelTa }}{{ $isAktifOption ? ' — Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Bulan
                    </label>

                    <input type="month"
                           id="bulanInput"
                           name="bulan"
                           value="{{ $bulan }}"
                           class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Terapkan
                    </button>

                    <a href="{{ $routeIndex }}"
                       class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-400">
                Default menampilkan tahun ajaran aktif. Gunakan filter tahun ajaran untuk melihat histori presensi.
            </p>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-medium text-gray-500">
                Persentase Kehadiran
            </div>

            <div class="mt-1 text-2xl font-bold text-blue-700">
                {{ number_format($summaryGlobal['persen_kehadiran'] ?? 0, 1) }}%
            </div>

            <div class="mt-1 text-[11px] leading-relaxed text-gray-500">
                Dihitung dari H dan A. Izin dan sakit tetap dicatat, tetapi tidak menurunkan persentase.
            </div>

            <div class="mt-1 text-[11px] text-gray-500">
                H: {{ $summaryGlobal['H'] ?? 0 }}
                • I: {{ $summaryGlobal['I'] ?? 0 }}
                • S: {{ $summaryGlobal['S'] ?? 0 }}
                • A: {{ $summaryGlobal['A'] ?? 0 }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-medium text-gray-500">
                Belum Memenuhi Kehadiran
            </div>

            <div class="mt-1 text-2xl font-bold text-red-600">
                {{ $summaryGlobal['siswa_perlu_perhatian'] ?? 0 }}
            </div>

            <div class="mt-1 text-[11px] leading-relaxed text-gray-500">
                Siswa dengan persentase kehadiran kurang dari 90%.
            </div>

            <div class="mt-1 text-[11px] text-gray-500">
                80% - 89,9% perlu perhatian, di bawah 80% perlu tindak lanjut.
            </div>
        </div>
    </div>

    {{-- Rekap per Rombel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-800">
                Rekap Presensi per Rombel
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Ringkasan presensi per rombel pada tahun ajaran
                <span class="font-semibold text-gray-700">{{ $tahunLabel }}</span>.
                Persentase kehadiran dihitung dari H dan A.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            No
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Rombel
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Jumlah Siswa
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            H
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            I
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            S
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            A
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Kehadiran
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Belum Memenuhi
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Mata Pelajaran
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($rombelRows as $row)
                        @php
                            $persenTampil = $row['persen_tampil'] ?? 100;
                        @endphp

                        <tr class="align-top transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $row['nama_rombel'] ?? '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-400">
                                    Tingkat {{ $row['tingkat'] ?? '-' }}
                                    @if(!empty($row['tahun_ajaran']))
                                        • {{ $row['tahun_ajaran'] }}
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center text-gray-700">
                                {{ $row['jumlah_siswa'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-emerald-700">
                                {{ $row['stats']['H'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-blue-700">
                                {{ $row['stats']['I'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-cyan-700">
                                {{ $row['stats']['S'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-red-700">
                                {{ $row['stats']['A'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBg($persenTampil) }}">
                                    {{ number_format($persenTampil, 1) }}%
                                </span>

                                <div class="mt-1 text-[10px] text-gray-400">
                                    H / (H + A)
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ ($row['perlu_perhatian'] ?? 0) > 0 ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                                    {{ $row['perlu_perhatian'] ?? 0 }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if(isset($row['mapel_list']) && $row['mapel_list']->count())
                                    <div class="flex max-w-xl flex-wrap gap-2">
                                        @foreach($row['mapel_list'] as $m)
                                            <a href="{{ route('kepala_sekolah.monitor.presensi.rombel-mapel', [
                                                    'rombel' => $row['id'],
                                                    'mapel' => $m->id,
                                                    'bulan' => $bulan,
                                                    'tahun_ajaran_id' => $tahunAjaranId,
                                                ]) }}"
                                               class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                {{ $m->nama_mapel ?? 'Mapel' }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        Belum ada mapel terjadwal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    Belum ada data presensi
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Belum ada data rombel atau presensi pada bulan {{ $labelBulan }} untuk tahun ajaran {{ $tahunLabel }}.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Siswa Belum Memenuhi Kehadiran --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-5 py-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Siswa Belum Memenuhi Kehadiran
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Daftar siswa dengan persentase kehadiran kurang dari 90%.
                        Izin dan sakit tetap dicatat, tetapi tidak menurunkan persentase.
                    </p>
                </div>

                <div class="rounded-xl border bg-white px-3 py-2 text-xs text-gray-500">
                    Maksimal 10 siswa dengan persentase terendah
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            No
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Siswa
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Rombel
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            H
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            I
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            S
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            A
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Kehadiran
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($studentAlerts as $s)
                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $s['nama'] ?? '-' }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    NIS: {{ $s['nis'] ?? '-' }}
                                    @if(!empty($s['nisn']))
                                        • NISN: {{ $s['nisn'] }}
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $s['rombel_nama'] ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-emerald-700">
                                {{ $s['stats']['H'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-blue-700">
                                {{ $s['stats']['I'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-cyan-700">
                                {{ $s['stats']['S'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-red-700">
                                {{ $s['stats']['A'] ?? 0 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBg($s['persen'] ?? 0) }}">
                                    {{ number_format($s['persen'] ?? 0, 1) }}%
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $s['status_class'] ?? 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                    {{ $s['status_label'] ?? 'Perlu Perhatian' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    Tidak ada siswa yang belum memenuhi kehadiran
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Semua siswa yang memiliki data presensi sudah memenuhi batas kehadiran 90%.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const filterPresensiForm = document.getElementById('filterPresensiForm');
    const autoFilters = document.querySelectorAll('.auto-filter');

    if (filterPresensiForm && autoFilters.length) {
        autoFilters.forEach((filter) => {
            filter.addEventListener('change', function () {
                filterPresensiForm.submit();
            });
        });
    }
</script>
@endsection