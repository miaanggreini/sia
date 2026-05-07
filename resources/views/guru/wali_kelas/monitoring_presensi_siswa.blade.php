@extends('layouts.guru')

@section('content')
<div class="w-full max-w-none mx-0 space-y-4">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Presensi Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">
                Riwayat presensi siswa per pertemuan pada mapel dan bulan yang dipilih.
            </p>
        </div>

        <a href="{{ route('guru.wali.monitoring-presensi', ['bulan' => $bulan, 'mapel_id' => $mapel->id]) }}"
           class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
            Kembali
        </a>
    </div>

    {{-- INFO SINGKAT --}}
    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 divide-y md:divide-y-0 md:divide-x">
            <div class="px-5 py-4 xl:col-span-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Siswa</div>
                <div class="mt-1 text-lg font-bold text-gray-900">{{ $siswa->nama ?? '-' }}</div>
                <div class="text-sm text-gray-500">NIS: {{ $siswa->nis ?? '-' }}</div>
            </div>

            <div class="px-5 py-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelas</div>
                <div class="mt-1 text-base font-semibold text-gray-900">{{ $rombel->nama_rombel ?? '-' }}</div>
            </div>

            <div class="px-5 py-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Mapel</div>
                <div class="mt-1 text-base font-semibold text-gray-900">{{ $mapel->nama_mapel ?? '-' }}</div>
            </div>

            <div class="px-5 py-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bulan</div>
                <div class="mt-1 text-base font-semibold text-gray-900">
                    {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulan, 'Asia/Jakarta')->translatedFormat('F Y') }}
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">% Hadir</div>
                <div class="mt-1 text-lg font-bold {{ $persenHadir >= 90 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ number_format($persenHadir, 1, '.', '') }}%
                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN --}}
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium">
            <span>Total Pertemuan</span>
            <span class="font-bold">{{ $total ?? 0 }}</span>
        </span>

        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
            <span>Hadir</span>
            <span class="font-bold">{{ $count['hadir'] ?? 0 }}</span>
        </span>

        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium">
            <span>Izin</span>
            <span class="font-bold">{{ $count['izin'] ?? 0 }}</span>
        </span>

        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-xs font-medium">
            <span>Sakit</span>
            <span class="font-bold">{{ $count['sakit'] ?? 0 }}</span>
        </span>

        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
            <span>Alfa</span>
            <span class="font-bold">{{ $count['alfa'] ?? 0 }}</span>
        </span>
    </div>

    {{-- TABEL RIWAYAT --}}
    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Riwayat Presensi</h2>
            <p class="text-sm text-gray-500 mt-1">
                Menampilkan daftar pertemuan beserta status kehadiran siswa.
            </p>
        </div>

        <div class="p-5">
            @if(empty($rows) || collect($rows)->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
                    Belum ada data presensi pada bulan ini untuk mapel tersebut.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="px-4 py-3 text-left rounded-l-xl w-16">No</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jam</th>
                                <th class="px-4 py-3 text-center rounded-r-xl">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rows as $i => $row)
                                @php
                                    $status = $row->status ?? 'alfa';

                                    $badge = match($status) {
                                        'hadir' => 'bg-emerald-100 text-emerald-700',
                                        'izin'  => 'bg-blue-100 text-blue-700',
                                        'sakit' => 'bg-cyan-100 text-cyan-700',
                                        'alfa'  => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };

                                    $label = match($status) {
                                        'hadir' => 'Hadir',
                                        'izin'  => 'Izin',
                                        'sakit' => 'Sakit',
                                        'alfa'  => 'Alfa',
                                        default => ucfirst($status),
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $row->tanggal ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->jam ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection