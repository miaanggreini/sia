@extends('layouts.admin')
@section('title','Detail Presensi Mapel')

@section('content')
@php
    use Carbon\Carbon;

    [$year,$month] = explode('-', $bulan);
    $labelBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
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

<div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
    <div class="px-5 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-800">Buku Absen Siswa ({{ $labelBulan }})</h2>
        <p class="text-sm text-gray-500 mt-1">Ringkasan harian dan total presensi siswa dalam satu tabel.</p>
    </div>

    <table class="min-w-full text-xs">
        <thead>
            <tr class="bg-indigo-600 text-white font-semibold">
                <th class="px-3 py-2 text-left w-10">No</th>
                <th class="px-3 py-2 text-left w-48">Nama Siswa</th>
                @foreach($days as $d)
                    <th class="px-1 py-2 text-center min-w-[24px]">{{ $d->day }}</th>
                @endforeach
                <th class="px-3 py-2 text-center w-14">H</th>
                <th class="px-3 py-2 text-center w-14">I</th>
                <th class="px-3 py-2 text-center w-14">S</th>
                <th class="px-3 py-2 text-center w-14">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $i => $row)
                @php
                    $rowMatrix = $matrix[$row->id] ?? [];
                    $h = $iizin = $s = $a = 0;
                    foreach ($rowMatrix as $kode) {
                        if ($kode === 'H') $h++;
                        elseif ($kode === 'I') $iizin++;
                        elseif ($kode === 'S') $s++;
                        elseif ($kode === 'A') $a++;
                    }
                @endphp
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-3 py-1.5 text-center">{{ $i + 1 }}</td>
                    <td class="px-3 py-1.5">
                        <div class="font-medium text-gray-900">{{ $row->nama }}</div>
                        <div class="text-[11px] text-gray-500">
                            NIS: {{ $row->nis ?? '-' }} • NISN: {{ $row->nisn ?? '-' }}
                        </div>
                    </td>

                    @foreach($days as $d)
                        @php $kode = $rowMatrix[$d->day] ?? ''; @endphp
                        <td class="px-1 py-1.5 text-center font-semibold">
                            @if($kode === 'H')
                                <span class="text-emerald-700">{{ $kode }}</span>
                            @elseif($kode === 'I')
                                <span class="text-blue-700">{{ $kode }}</span>
                            @elseif($kode === 'S')
                                <span class="text-cyan-700">{{ $kode }}</span>
                            @elseif($kode === 'A')
                                <span class="text-rose-700">{{ $kode }}</span>
                            @else
                                <span class="text-gray-300">•</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="px-3 py-1.5 text-center">{{ $h }}</td>
                    <td class="px-3 py-1.5 text-center">{{ $iizin }}</td>
                    <td class="px-3 py-1.5 text-center">{{ $s }}</td>
                    <td class="px-3 py-1.5 text-center">{{ $a }}</td>
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
@endsection