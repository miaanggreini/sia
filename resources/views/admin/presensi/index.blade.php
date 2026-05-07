@extends('layouts.admin')

@section('title','Monitoring Presensi')

@section('content')
@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    [$year, $month] = explode('-', $bulan);

    $labelBulan = Carbon::createFromDate($year, $month, 1)
        ->locale('id')
        ->translatedFormat('F Y');

    $statusBg = function ($persen) {
        if ($persen >= 90) {
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        }

        if ($persen >= 80) {
            return 'bg-amber-100 text-amber-700 border-amber-200';
        }

        return 'bg-rose-100 text-rose-700 border-rose-200';
    };

    $namaTahunDipilih = $taDipilih->nama_tahun
        ?? $taDipilih->nama
        ?? $taDipilih->tahun
        ?? $taDipilih->label
        ?? '-';
@endphp

<div class="mb-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 md:text-3xl">
                Monitoring Presensi
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Rekap monitoring presensi siswa bulan
                <span class="font-semibold text-indigo-700">{{ $labelBulan }}</span>
                • Tahun ajaran:
                <span class="font-semibold text-indigo-700">
                    {{ $namaTahunDipilih }}
                    @if(($taDipilih->status ?? null) === 'aktif')
                        — Aktif
                    @endif
                </span>
            </p>
        </div>

        <div class="rounded-xl border bg-white px-4 py-2 text-xs text-gray-500 shadow-sm">
            Periode:
            {{ $start->locale('id')->translatedFormat('d M Y') }}
            s.d.
            {{ $end->locale('id')->translatedFormat('d M Y') }}
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="mb-5 rounded-2xl border bg-white p-4 shadow-sm">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Tahun Ajaran
                </label>

                <select name="tahun_ajaran_id"
                        class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($daftarTahunAjaran as $ta)
                        @php
                            $labelTa = $ta->nama_tahun
                                ?? $ta->nama
                                ?? $ta->tahun
                                ?? $ta->label
                                ?? '-';
                        @endphp

                        <option value="{{ $ta->id }}" @selected((int) $tahunAjaranId === (int) $ta->id)>
                            {{ $labelTa }}
                            @if(($ta->status ?? null) === 'aktif')
                                — Aktif
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Bulan
                </label>

                <input type="month"
                       name="bulan"
                       value="{{ $bulan }}"
                       class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-end justify-start gap-3 lg:col-span-5 lg:justify-end">
                <button type="submit"
                        class="inline-flex min-w-[180px] items-center justify-center rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Terapkan
                </button>

                <a href="{{ route('admin.presensi.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </div>

        <div class="text-xs text-gray-500">
            Default menampilkan tahun ajaran aktif. Gunakan filter tahun ajaran untuk melihat histori presensi.
        </div>
    </form>
</div>

{{-- SUMMARY --}}
<div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="rounded-2xl border bg-white px-4 py-3 shadow-sm">
        <div class="text-xs font-medium text-gray-500">
            Persentase Kehadiran
        </div>

        <div class="mt-1 text-2xl font-bold text-indigo-700">
            {{ number_format($summaryGlobal['persen_kehadiran'] ?? 0, 1) }}%
        </div>
    </div>

    <div class="rounded-2xl border bg-white px-4 py-3 shadow-sm">
        <div class="text-xs font-medium text-gray-500">
            Belum Memenuhi Kehadiran
        </div>

        <div class="mt-1 text-2xl font-bold text-rose-600">
            {{ $summaryGlobal['siswa_perlu_perhatian'] ?? 0 }}
        </div>

    </div>
</div>

{{-- REKAP PER ROMBEL --}}
<div class="mb-5 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b bg-gray-50 px-5 py-4">
        <h2 class="text-lg font-semibold text-gray-800">
            Rekap Presensi per Rombel
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Ringkasan presensi per rombel pada tahun ajaran {{ $namaTahunDipilih }}.
            Persentase kehadiran dihitung dari H dan A.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        No
                    </th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        Rombel
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        Jumlah Siswa
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        H
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        I
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        S
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        A
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        Kehadiran
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        Belum Memenuhi
                    </th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        Mata Pelajaran
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($rombelSummaries as $row)
                    <tr class="align-top hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">
                                {{ $row['nama_rombel'] }}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-400">
                                Tingkat {{ $row['tingkat'] ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $row['jumlah_siswa'] }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-emerald-700">
                            {{ $row['stats']['H'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-blue-700">
                            {{ $row['stats']['I'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-cyan-700">
                            {{ $row['stats']['S'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-rose-700">
                            {{ $row['stats']['A'] ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBg($row['persen']) }}">
                                {{ number_format($row['persen'], 1) }}%
                            </span>

                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ ($row['perlu_perhatian'] ?? 0) > 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                {{ $row['perlu_perhatian'] ?? 0 }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            @if(($row['mapel_list'] ?? collect())->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach($row['mapel_list'] as $m)
                                        <a href="{{ route('admin.presensi.rombel-mapel', [
                                                'rombel' => $row['id'],
                                                'mapel' => $m->id,
                                                'bulan' => $bulan,
                                                'tahun_ajaran_id' => $tahunAjaranId,
                                            ]) }}"
                                           class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100">
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
                        <td colspan="10" class="px-5 py-10 text-center">
                            <div class="mx-auto max-w-md">
                                <div class="text-sm font-semibold text-gray-700">
                                    Belum ada data presensi
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    Data presensi akan tampil setelah guru membuat sesi presensi dan siswa melakukan presensi.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SISWA BELUM MEMENUHI KEHADIRAN --}}
<div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
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
        <table class="min-w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        No
                    </th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        Siswa
                    </th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                        Rombel
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        H
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        I
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        S
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        A
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        Kehadiran
                    </th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">
                        Status
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($studentAlerts as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-5 py-3">
                            <div class="font-semibold text-gray-800">
                                {{ $row['nama'] }}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-400">
                                NIS: {{ $row['nis'] ?? '-' }}
                                @if(!empty($row['nisn']))
                                    • NISN: {{ $row['nisn'] }}
                                @endif
                            </div>
                        </td>

                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-700">
                                {{ $row['rombel_nama'] ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-emerald-700">
                            {{ $row['stats']['H'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-blue-700">
                            {{ $row['stats']['I'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-cyan-700">
                            {{ $row['stats']['S'] ?? 0 }}
                        </td>

                        <td class="px-3 py-3 text-center font-semibold text-rose-700">
                            {{ $row['stats']['A'] ?? 0 }}
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBg($row['persen']) }}">
                                {{ number_format($row['persen'], 1) }}%
                            </span>
                        </td>

                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $row['status_class'] }}">
                                {{ $row['status_label'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center">
                            <div class="mx-auto max-w-md">
                                <div class="text-sm font-semibold text-gray-700">
                                    Tidak ada siswa yang perlu perhatian
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    Semua siswa yang memiliki data presensi sudah memenuhi batas kehadiran 90%.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection