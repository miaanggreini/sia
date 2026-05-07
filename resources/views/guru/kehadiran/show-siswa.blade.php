@extends('layouts.guru')

@section('content')
@php
    $statusLabel = [
        'hadir' => ['label' => 'Hadir', 'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
        'izin'  => ['label' => 'Izin',  'class' => 'bg-blue-50 text-blue-700 border border-blue-200'],
        'sakit' => ['label' => 'Sakit', 'class' => 'bg-cyan-50 text-cyan-700 border border-cyan-200'],
        'alfa'  => ['label' => 'Alfa',  'class' => 'bg-red-50 text-red-700 border border-red-200'],
    ];
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Riwayat Presensi Siswa</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $siswa->nama ?? '-' }} · {{ $siswa->nis ?? '-' }} · {{ $rombel->nama_rombel ?? '-' }} · {{ $mapel->nama_mapel ?? '-' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guru.kehadiran.index', [
                'rombel_id' => $rombel->id ?? null,
                'mata_pelajaran_id' => $mapel->id ?? null,
            ]) }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </div>

    {{-- Ringkasan statistik --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Total Pertemuan</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $total }}</div>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <div class="text-sm text-emerald-700">Hadir</div>
            <div class="mt-2 text-2xl font-bold text-emerald-800">{{ $count['hadir'] }}</div>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <div class="text-sm text-blue-700">Izin</div>
            <div class="mt-2 text-2xl font-bold text-blue-800">{{ $count['izin'] }}</div>
        </div>

        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 shadow-sm">
            <div class="text-sm text-cyan-700">Sakit</div>
            <div class="mt-2 text-2xl font-bold text-cyan-800">{{ $count['sakit'] }}</div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="text-sm text-red-700">Alfa</div>
            <div class="mt-2 text-2xl font-bold text-red-800">{{ $count['alfa'] }}</div>
        </div>
    </div>

    {{-- Persentase --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Persentase Kehadiran</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Persentase kehadiran dihitung dari total hadir dibanding total seluruh pertemuan.
                </p>
            </div>

            <div class="text-3xl font-bold {{ $persenHadir >= 75 ? 'text-emerald-600' : ($persenHadir >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                {{ number_format($persenHadir, 1) }}%
            </div>
        </div>

        <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full {{ $persenHadir >= 75 ? 'bg-emerald-500' : ($persenHadir >= 50 ? 'bg-amber-400' : 'bg-red-500') }}"
                 style="width: {{ max(0, min(100, $persenHadir)) }}%;">
            </div>
        </div>
    </div>

    {{-- Tabel riwayat --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Kehadiran</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">No</th>
                        <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-5 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $i => $row)
                        @php
                            $status = strtolower($row->status ?? 'alfa');
                            $badge = $statusLabel[$status] ?? $statusLabel['alfa'];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-700">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 text-slate-700">
                                {{ \Illuminate\Support\Carbon::parse($row->mulai_pada)->timezone('Asia/Jakarta')->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-500">
                                Belum ada data riwayat kehadiran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection