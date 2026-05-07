{{-- resources/views/kepsek/rekap_rombel/detail_menu.blade.php --}}
@extends('layouts.kepsek')

@section('title', 'Detail Menu Rombel')

@section('content')
@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $formatTanggal = function ($value) {
        if (empty($value)) {
            return '—';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d M Y H:i');
        } catch (\Exception $e) {
            return '—';
        }
    };

    $mulaiStr = $tanggal_mulai
        ?? $formatTanggal($periode->tanggal_mulai ?? $periode->mulai ?? null);

    $selesaiStr = $tanggal_selesai
        ?? $formatTanggal($periode->tanggal_selesai ?? $periode->selesai ?? null);

    $totalSiswa = $pendaftar->count();

    $namaMenu = $menu->nama ?? $menu->nama_menu ?? 'Menu Rombel';
@endphp

<div class="w-full space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b pb-5 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Menu — {{ $namaMenu }}
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                <span class="font-semibold text-gray-700">Periode:</span>
                {{ $mulaiStr }} — {{ $selesaiStr }}
            </p>
        </div>

        <a href="{{ route('kepala_sekolah.rekap_rombel.show', $periode->id) }}"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            Kembali
        </a>
    </div>

        </div>
    </div>

    {{-- Kartu Data Siswa Diterima --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        {{-- Header Card --}}
        <div class="border-b bg-gray-50 px-6 py-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Siswa yang Diterima di Menu Ini
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Menampilkan siswa yang diterima pada menu rombel ini berdasarkan hasil penempatan.
                    </p>
                </div>

                <span class="inline-flex rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                    Total: {{ $totalSiswa }} siswa
                </span>
            </div>
        </div>

        @if($pendaftar->isEmpty())
            <div class="px-6 py-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-7 w-7"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M17 20h-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3H5v-3a4 4 0 014-4h6a4 4 0 014 4v3z" />
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 10a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-semibold text-gray-800">
                    Belum ada siswa yang diterima
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Belum ada siswa yang diterima di menu ini pada periode tersebut.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">No</th>
                            <th class="px-6 py-3 text-left font-semibold">Nama Siswa</th>
                            <th class="px-6 py-3 text-left font-semibold">NIS</th>
                            <th class="px-6 py-3 text-left font-semibold">NISN</th>
                            <th class="px-6 py-3 text-left font-semibold">Rombel</th>
                            <th class="px-6 py-3 text-center font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                        @foreach($pendaftar as $i => $row)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-4 align-top text-gray-500">
                                    {{ $i + 1 }}
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="font-semibold text-gray-900">
                                        {{ $row->siswa->nama ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top text-gray-600">
                                    {{ $row->siswa->nis ?? '-' }}
                                </td>

                                <td class="px-6 py-4 align-top text-gray-600">
                                    {{ $row->siswa->nisn ?? '-' }}
                                </td>

                                <td class="px-6 py-4 align-top text-gray-600">
                                    {{ $row->nama_rombel ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-center align-top">
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $row->status_tampilan ?? 'Diterima' }}
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
@endsection