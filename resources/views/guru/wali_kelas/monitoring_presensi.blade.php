@extends('layouts.guru')

@section('content')
<div class="w-full max-w-none mx-0 space-y-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Monitoring Presensi (Wali Kelas)</h1>
        <p class="text-sm text-gray-500 mt-1">
            Ringkasan presensi per siswa dalam bulan terpilih. Klik <span class="font-semibold">Detail</span> untuk melihat riwayat pertemuan.
        </p>
    </div>

    @if(!$rombel)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            <div class="font-semibold text-sm">Belum terdaftar sebagai wali kelas.</div>
            <div class="text-sm mt-1">
                Anda belum tercatat sebagai wali kelas pada tahun ajaran aktif.
            </div>
        </div>
    @else
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Kelas Wali (TA Aktif)
                        </div>
                        <div class="mt-1 text-xl font-bold text-gray-900">
                            {{ $rombel->nama_rombel ?? '-' }}
                        </div>
                        <div class="mt-1 text-sm text-gray-500">
                            Tingkat {{ $rombel->tingkat ?? '-' }}
                            • TA {{ $rombel->tahun_ajaran ?? $rombel->tahunAjaran->nama_tahun ?? '-' }}
                        </div>
                    </div>

                    <form method="GET" class="flex flex-col sm:flex-row gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                            <input type="month"
                                   name="bulan"
                                   value="{{ $bulan }}"
                                   class="rounded-xl border-gray-200 text-sm w-full sm:w-auto">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mata Pelajaran</label>
                            <select name="mapel_id"
                                    class="rounded-xl border-gray-200 text-sm min-w-[180px] w-full sm:w-auto">
                                <option value="">— Semua Mapel —</option>
                                @foreach($daftarMapel as $m)
                                    <option value="{{ $m->id }}" @selected($mapelId == $m->id)>
                                        {{ $m->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                                Terapkan
                            </button>
                            <a href="{{ route('guru.wali.monitoring-presensi') }}"
                               class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                @php
                    $totalSiswa = count($rows ?? []);
                    $perluPerhatian = collect($rows ?? [])->where('status_label', 'Perlu perhatian')->count();
                    $rataPersen = collect($rows ?? [])->avg('persentase');
                @endphp

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium">
                        <span>Total Siswa</span>
                        <span class="font-bold">{{ $totalSiswa }}</span>
                    </span>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-medium">
                        <span>Perlu perhatian</span>
                        <span class="font-bold">{{ $perluPerhatian }}</span>
                    </span>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-medium">
                        <span>Rata-rata hadir</span>
                        <span class="font-bold">
                            {{ $rataPersen !== null ? number_format($rataPersen, 1, '.', '') : '0.0' }}%
                        </span>
                    </span>
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-800">Rekap Presensi per Siswa</h2>
                    <span class="text-xs text-gray-500">{{ count($rows ?? []) }} siswa</span>
                </div>

                @if(empty($rows) || collect($rows)->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
                        Belum ada data presensi pada bulan ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700">
                                    <th class="px-4 py-3 text-left rounded-l-xl w-14">No</th>
                                    <th class="px-4 py-3 text-left">Siswa</th>
                                    <th class="px-4 py-3 text-center w-20">H</th>
                                    <th class="px-4 py-3 text-center w-20">I</th>
                                    <th class="px-4 py-3 text-center w-20">S</th>
                                    <th class="px-4 py-3 text-center w-20">A</th>
                                    <th class="px-4 py-3 text-center w-28">% Hadir</th>
                                    <th class="px-4 py-3 text-center w-36">Status</th>
                                    <th class="px-4 py-3 text-center rounded-r-xl w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($rows as $i => $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>

                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ $row->nama ?? '-' }}</div>
                                            <div class="text-sm text-gray-500">NIS: {{ $row->nis ?? '-' }}</div>
                                        </td>

                                        <td class="px-4 py-3 text-center font-medium text-emerald-700">{{ $row->hadir ?? 0 }}</td>
                                        <td class="px-4 py-3 text-center font-medium text-blue-700">{{ $row->izin ?? 0 }}</td>
                                        <td class="px-4 py-3 text-center font-medium text-cyan-700">{{ $row->sakit ?? 0 }}</td>
                                        <td class="px-4 py-3 text-center font-medium text-rose-700">{{ $row->alfa ?? 0 }}</td>

                                        <td class="px-4 py-3 text-center font-semibold text-gray-900">
                                            {{ number_format($row->persentase ?? 0, 1, '.', '') }}%
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @if(($row->status_label ?? '') === 'Perlu perhatian')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                                    Perlu perhatian
                                                </span>
                                            @elseif(($row->status_label ?? '') === 'Aman')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                    Aman
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                    Belum ada data
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @if(!empty($row->detail_url))
                                                <a href="{{ $row->detail_url }}"
                                                   class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                                                    Detail
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-100 text-gray-400 text-sm font-medium">
                                                    Pilih mapel
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        Keterangan: H = Hadir, I = Izin, S = Sakit, A = Alfa.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection