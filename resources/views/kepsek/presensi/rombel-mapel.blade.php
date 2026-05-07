@extends('layouts.kepsek')
@section('title','Detail Presensi Rombel & Mapel')

@section('content')
@php
    use Carbon\Carbon;

    [$year,$month] = explode('-', $bulan);
    $labelBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
@endphp

<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Detail Presensi Rombel & Mapel</h1>
        <p class="text-sm text-gray-500 mt-1">
            Rombel:
            <span class="font-semibold text-gray-800">{{ $rombel->nama_rombel ?? '-' }}</span>
            • Mapel:
            <span class="font-semibold text-indigo-700">{{ $mapel->nama_mapel ?? '-' }}</span>
            • Bulan:
            <span class="font-semibold text-indigo-700">{{ $labelBulan }}</span>
        </p>
    </div>

    <a href="{{ route('kepala_sekolah.monitor.presensi.index', ['bulan' => $bulan]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 shadow-sm text-gray-700">
        Kembali
    </a>
</div>

<div class="bg-white border rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET"
          action="{{ route('kepala_sekolah.monitor.presensi.rombel-mapel', ['rombel' => $rombel->id, 'mapel' => $mapel->id]) }}"
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

            <a href="{{ route('kepala_sekolah.monitor.presensi.rombel-mapel', ['rombel' => $rombel->id, 'mapel' => $mapel->id]) }}"
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

<div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-800">Buku Absen Siswa ({{ $labelBulan }})</h2>
        <p class="text-sm text-gray-500 mt-1">Ringkasan harian dan total presensi siswa dalam satu tabel.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead>
                <tr class="bg-indigo-600 text-white font-semibold">
                    <th class="px-4 py-2 text-left w-12">No</th>
                    <th class="px-4 py-2 text-left min-w-[220px]">Nama Siswa</th>
                    @foreach($days as $day)
                        <th class="px-1 py-2 text-center min-w-[28px] border-l border-indigo-500">
                            {{ $day->day }}
                        </th>
                    @endforeach
                    <th class="px-3 py-2 text-center w-10 bg-indigo-700 border-l border-indigo-500">H</th>
                    <th class="px-3 py-2 text-center w-10 bg-indigo-700">I</th>
                    <th class="px-3 py-2 text-center w-10 bg-indigo-700">S</th>
                    <th class="px-3 py-2 text-center w-10 bg-indigo-700">A</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($siswa as $idx => $s)
                    @php
                        $sid = $s->id;
                        $rowMatrix = $matrix[$sid] ?? [];
                        $countH = $countI = $countS = $countA = 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-500">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 text-gray-900 whitespace-nowrap">
                            <div class="font-medium">{{ $s->nama }}</div>
                            <div class="text-[10px] text-gray-500 font-mono">
                                NIS: {{ $s->nis ?? '-' }} · NISN: {{ $s->nisn ?? '-' }}
                            </div>
                        </td>

                        @foreach($days as $day)
                            @php
                                $d    = (int) $day->format('j');
                                $kode = $rowMatrix[$d] ?? '';
                                if ($kode === 'H') $countH++;
                                elseif ($kode === 'I') $countI++;
                                elseif ($kode === 'S') $countS++;
                                elseif ($kode === 'A') $countA++;
                            @endphp

                            <td class="px-1 py-1 text-center border-l border-gray-100">
                                @if($kode)
                                    <span class="text-[11px] font-bold
                                        {{ $kode === 'H' ? 'text-emerald-700' : '' }}
                                        {{ $kode === 'I' ? 'text-blue-700' : '' }}
                                        {{ $kode === 'S' ? 'text-cyan-700' : '' }}
                                        {{ $kode === 'A' ? 'text-rose-700' : '' }}
                                    ">
                                        {{ $kode }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-gray-300">•</span>
                                @endif
                            </td>
                        @endforeach

                        <td class="px-3 py-2 text-center text-xs font-semibold {{ $countH > 0 ? 'text-emerald-700' : 'text-gray-500' }} border-l border-gray-100">{{ $countH }}</td>
                        <td class="px-3 py-2 text-center text-xs font-semibold {{ $countI > 0 ? 'text-blue-700' : 'text-gray-500' }}">{{ $countI }}</td>
                        <td class="px-3 py-2 text-center text-xs font-semibold {{ $countS > 0 ? 'text-cyan-700' : 'text-gray-500' }}">{{ $countS }}</td>
                        <td class="px-3 py-2 text-center text-xs font-semibold {{ $countA > 0 ? 'text-rose-700' : 'text-gray-500' }}">{{ $countA }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + count($days) + 4 }}" class="px-4 py-6 text-center text-gray-500">
                            Belum ada siswa di rombel ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection