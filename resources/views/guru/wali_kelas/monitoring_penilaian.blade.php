@extends('layouts.guru')

@section('title', 'Monitoring Penilaian (Wali Kelas)')

@section('content')
<div class="w-full max-w-none mx-0 space-y-4">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Monitoring Penilaian (Wali Kelas)
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Klik <span class="font-semibold">Detail</span> untuk melihat nilai siswa berdasarkan semester.
        </p>
    </div>

    @if(!$rombel)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            <div class="text-sm font-semibold">
                Belum terdaftar sebagai wali kelas.
            </div>
            <div class="mt-1 text-sm">
                Anda belum tercatat sebagai wali kelas pada tahun ajaran aktif.
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

            {{-- INFO KELAS --}}
            <div class="border-b px-5 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            Kelas Wali (TA Aktif)
                        </div>

                        <div class="mt-1 text-xl font-bold text-gray-900">
                            {{ $rombel->nama_rombel ?? '-' }}
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            Tingkat {{ $rombel->tingkat ?? '-' }}
                            • TA {{ $rombel->tahunAjaran->nama_tahun ?? $taAktif->nama_tahun ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-indigo-50 px-4 py-3 text-right">
                        <div class="text-xs text-indigo-600">
                            Jumlah Siswa
                        </div>
                        <div class="mt-1 text-2xl font-bold text-indigo-700">
                            {{ collect($rows)->count() }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL SISWA --}}
            <div class="px-5 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">
                        Daftar Siswa
                    </h2>
                    <span class="text-xs text-gray-500">
                        {{ collect($rows)->count() }} siswa
                    </span>
                </div>

                @if(empty($rows) || collect($rows)->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
                        Belum ada siswa aktif pada kelas wali ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700">
                                    <th class="w-16 rounded-l-xl px-4 py-3 text-left">No</th>
                                    <th class="px-4 py-3 text-left">Siswa</th>
                                    <th class="px-4 py-3 text-left">NISN</th>
                                    <th class="w-40 rounded-r-xl px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @foreach($rows as $i => $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $i + 1 }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ $row->siswa_nama ?? '-' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                NIS: {{ $row->siswa_nis ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $row->siswa_nisn ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('guru.wali.monitoring-penilaian.detail', $row->siswa_id) }}"
                                               class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection