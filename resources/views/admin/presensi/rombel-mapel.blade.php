@extends('layouts.admin')
@section('title','Detail Presensi Mapel')

@section('content')
@php
    use Carbon\Carbon;

    [$year, $month] = explode('-', $bulan);
    $labelBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

    /*
     * Penanda hari libur reguler.
     * Carbon::SATURDAY = 6
     * Carbon::SUNDAY   = 0
     */
    $isWeekend = function ($date) {
        return in_array((int) $date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true);
    };
@endphp

<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
            Presensi {{ $mapel->nama_mapel ?? 'Mapel' }} – {{ $rombel->nama_rombel ?? 'Rombel' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Rekap hadir per siswa bulan
            <span class="font-semibold text-indigo-700">{{ $labelBulan }}</span>.
        </p>
    </div>

    <a href="{{ route('admin.presensi.index', ['bulan' => $bulan]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 shadow-sm text-gray-700">
        Kembali
    </a>
</div>

<div class="bg-white border rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET"
          action="{{ route('admin.presensi.rombel-mapel', [$rombel->id, $mapel->id]) }}"
          class="flex flex-col md:flex-row md:items-end gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
            <input type="month"
                   name="bulan"
                   value="{{ $bulan }}"
                   class="rounded-xl border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="flex items-center gap-2">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Terapkan
            </button>

            <a href="{{ route('admin.presensi.rombel-mapel', [$rombel->id, $mapel->id]) }}"
               class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border rounded-2xl p-4 shadow-sm">
        <div class="text-sm text-gray-500">Hadir</div>
        <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $rekapBulanan['H'] ?? 0 }}</div>
    </div>

    <div class="bg-white border rounded-2xl p-4 shadow-sm">
        <div class="text-sm text-gray-500">Izin</div>
        <div class="text-2xl font-bold text-blue-700 mt-1">{{ $rekapBulanan['I'] ?? 0 }}</div>
    </div>

    <div class="bg-white border rounded-2xl p-4 shadow-sm">
        <div class="text-sm text-gray-500">Sakit</div>
        <div class="text-2xl font-bold text-cyan-700 mt-1">{{ $rekapBulanan['S'] ?? 0 }}</div>
    </div>

    <div class="bg-white border rounded-2xl p-4 shadow-sm">
        <div class="text-sm text-gray-500">Alfa</div>
        <div class="text-2xl font-bold text-rose-700 mt-1">{{ $rekapBulanan['A'] ?? 0 }}</div>
    </div>
</div>

<div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b bg-gray-50">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Buku Absen Siswa ({{ $labelBulan }})
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan harian dan total presensi siswa dalam satu tabel.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700 border border-emerald-100">
                    H = Hadir
                </span>
                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700 border border-blue-100">
                    I = Izin
                </span>
                <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 font-semibold text-cyan-700 border border-cyan-100">
                    S = Sakit
                </span>
                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 font-semibold text-rose-700 border border-rose-100">
                    A = Alfa
                </span>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 font-semibold text-sky-700 border border-sky-100">
                    Kolom biru muda = Sabtu/Minggu
                </span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead>
                <tr class="bg-indigo-600 text-white font-semibold">
                    <th class="sticky left-0 z-20 bg-indigo-600 px-3 py-2 text-left w-10">
                        No
                    </th>

                    <th class="sticky left-10 z-20 bg-indigo-600 px-3 py-2 text-left min-w-[220px]">
                        Nama Siswa
                    </th>

                    @foreach($days as $d)
                        @php
                            $libur = $isWeekend($d);
                        @endphp

                        <th title="{{ $d->translatedFormat('l, d F Y') }}{{ $libur ? ' - Sabtu/Minggu' : '' }}"
                            class="px-1 py-2 text-center min-w-[30px] border-l
                                   {{ $libur
                                        ? 'bg-sky-100 text-sky-700 border-sky-200'
                                        : 'bg-indigo-600 text-white border-indigo-500/30' }}">
                            {{ $d->day }}
                        </th>
                    @endforeach

                    <th class="px-3 py-2 text-center w-14 bg-emerald-50 text-emerald-700 border-l border-emerald-100">
                        H
                    </th>
                    <th class="px-3 py-2 text-center w-14 bg-blue-50 text-blue-700 border-l border-blue-100">
                        I
                    </th>
                    <th class="px-3 py-2 text-center w-14 bg-cyan-50 text-cyan-700 border-l border-cyan-100">
                        S
                    </th>
                    <th class="px-3 py-2 text-center w-14 bg-rose-50 text-rose-700 border-l border-rose-100">
                        A
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse($siswa as $i => $row)
                    @php
                        $rowMatrix = $matrix[$row->id] ?? [];

                        $h = 0;
                        $iizin = 0;
                        $s = 0;
                        $a = 0;

                        foreach ($rowMatrix as $kode) {
                            if ($kode === 'H') {
                                $h++;
                            } elseif ($kode === 'I') {
                                $iizin++;
                            } elseif ($kode === 'S') {
                                $s++;
                            } elseif ($kode === 'A') {
                                $a++;
                            }
                        }
                    @endphp

                    <tr class="border-t hover:bg-slate-50">
                        <td class="sticky left-0 z-10 bg-white px-3 py-1.5 text-center">
                            {{ $i + 1 }}
                        </td>

                        <td class="sticky left-10 z-10 bg-white px-3 py-1.5">
                            <div class="font-medium text-gray-900">
                                {{ $row->nama }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                NIS: {{ $row->nis ?? '-' }} • NISN: {{ $row->nisn ?? '-' }}
                            </div>
                        </td>

                        @foreach($days as $d)
                            @php
                                $kode = $rowMatrix[$d->day] ?? '';
                                $libur = $isWeekend($d);
                            @endphp

                            <td title="{{ $d->translatedFormat('l, d F Y') }}{{ $libur ? ' - Sabtu/Minggu' : '' }}"
                                class="px-1 py-1.5 text-center font-semibold border-l border-gray-100
                                       {{ $libur ? 'bg-sky-50' : 'bg-white' }}">
                                @if($kode === 'H')
                                    <span class="text-emerald-700">{{ $kode }}</span>
                                @elseif($kode === 'I')
                                    <span class="text-blue-700">{{ $kode }}</span>
                                @elseif($kode === 'S')
                                    <span class="text-cyan-700">{{ $kode }}</span>
                                @elseif($kode === 'A')
                                    <span class="text-rose-700">{{ $kode }}</span>
                                @else
                                    <span class="{{ $libur ? 'text-sky-300' : 'text-gray-300' }}">•</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="px-3 py-1.5 text-center font-semibold text-emerald-700 bg-emerald-50/40 border-l border-emerald-100">
                            {{ $h }}
                        </td>
                        <td class="px-3 py-1.5 text-center font-semibold text-blue-700 bg-blue-50/40 border-l border-blue-100">
                            {{ $iizin }}
                        </td>
                        <td class="px-3 py-1.5 text-center font-semibold text-cyan-700 bg-cyan-50/40 border-l border-cyan-100">
                            {{ $s }}
                        </td>
                        <td class="px-3 py-1.5 text-center font-semibold text-rose-700 bg-rose-50/40 border-l border-rose-100">
                            {{ $a }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + count($days) + 4 }}"
                            class="px-3 py-4 text-center text-gray-400">
                            Belum ada siswa di rombel ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection