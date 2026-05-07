@extends('layouts.guru')

@section('title', 'Detail Penilaian Siswa')

@section('content')
<div class="w-full max-w-none mx-0 space-y-5">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Detail Penilaian Siswa
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Menampilkan nilai {{ $siswa->nama ?? '-' }} pada semester {{ $semester }}.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-700">
                Semester:
            </span>

            <a href="{{ route('guru.wali.monitoring-penilaian.detail', [
                    'siswa' => $siswa->id,
                    'semester' => 'Ganjil',
                ]) }}"
               class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                    {{ $semester === 'Ganjil'
                        ? 'border-indigo-600 bg-indigo-600 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                Ganjil
            </a>

            <a href="{{ route('guru.wali.monitoring-penilaian.detail', [
                    'siswa' => $siswa->id,
                    'semester' => 'Genap',
                ]) }}"
               class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                    {{ $semester === 'Genap'
                        ? 'border-indigo-600 bg-indigo-600 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                Genap
            </a>

            <a href="{{ route('guru.wali.monitoring-penilaian') }}"
               class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </div>

    {{-- INFO SISWA --}}
    <div class="rounded-2xl border bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Nama Siswa
                </div>
                <div class="mt-1 font-bold text-gray-900">
                    {{ $siswa->nama ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    NIS
                </div>
                <div class="mt-1 font-bold text-gray-900">
                    {{ $siswa->nis ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Kelas
                </div>
                <div class="mt-1 font-bold text-gray-900">
                    {{ $rombel->nama_rombel ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Tahun Ajaran
                </div>
                <div class="mt-1 font-bold text-gray-900">
                    {{ $taAktif->nama_tahun ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL SEMUA MAPEL --}}
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-800">
                Rincian Nilai {{ $semester }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Nilai ditampilkan per mata pelajaran, per lingkup materi, beserta nilai akhir dan predikat.
            </p>
        </div>

        <div class="p-5">
            @if(empty($rows) || collect($rows)->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
                    Belum ada data penilaian untuk siswa ini pada semester {{ $semester }}.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="bg-indigo-600 text-white">
                                <th class="rounded-tl-xl px-4 py-3 text-left">Mata Pelajaran</th>
                                <th class="px-4 py-3 text-left">Lingkup Materi</th>
                                <th class="px-4 py-3 text-center">TP1</th>
                                <th class="px-4 py-3 text-center">TP2</th>
                                <th class="px-4 py-3 text-center">TP3</th>
                                <th class="px-4 py-3 text-center">TP4</th>
                                <th class="rounded-tr-xl px-4 py-3 text-center">Nilai LM</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            @foreach($rows as $row)
                                @php
                                    $lmRows = [
                                        [
                                            'label' => 'Lingkup Materi 1',
                                            'tp1' => $row->lm1_tp1 ?? null,
                                            'tp2' => $row->lm1_tp2 ?? null,
                                            'tp3' => $row->lm1_tp3 ?? null,
                                            'tp4' => $row->lm1_tp4 ?? null,
                                            'nilai' => $row->lm1_nilai ?? null,
                                        ],
                                        [
                                            'label' => 'Lingkup Materi 2',
                                            'tp1' => $row->lm2_tp1 ?? null,
                                            'tp2' => $row->lm2_tp2 ?? null,
                                            'tp3' => $row->lm2_tp3 ?? null,
                                            'tp4' => $row->lm2_tp4 ?? null,
                                            'nilai' => $row->lm2_nilai ?? null,
                                        ],
                                        [
                                            'label' => 'Lingkup Materi 3',
                                            'tp1' => $row->lm3_tp1 ?? null,
                                            'tp2' => $row->lm3_tp2 ?? null,
                                            'tp3' => $row->lm3_tp3 ?? null,
                                            'tp4' => $row->lm3_tp4 ?? null,
                                            'nilai' => $row->lm3_nilai ?? null,
                                        ],
                                        [
                                            'label' => 'Lingkup Materi 4',
                                            'tp1' => $row->lm4_tp1 ?? null,
                                            'tp2' => $row->lm4_tp2 ?? null,
                                            'tp3' => $row->lm4_tp3 ?? null,
                                            'tp4' => $row->lm4_tp4 ?? null,
                                            'nilai' => $row->lm4_nilai ?? null,
                                        ],
                                    ];
                                @endphp

                                @foreach($lmRows as $index => $lm)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        @if($index === 0)
                                            <td rowspan="4"
                                                class="min-w-[180px] border-b border-gray-100 bg-gray-50/50 px-4 py-3 align-top font-semibold text-gray-800">
                                                {{ $row->mapel_nama ?? '-' }}
                                                @if(!empty($row->guru_nama))
                                                    <div class="mt-1 text-xs font-normal text-gray-500">
                                                        {{ $row->guru_nama }}
                                                    </div>
                                                @endif
                                            </td>
                                        @endif

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $lm['label'] }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            {{ $lm['tp1'] !== null ? number_format((float) $lm['tp1'], 2, '.', '') : '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            {{ $lm['tp2'] !== null ? number_format((float) $lm['tp2'], 2, '.', '') : '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            {{ $lm['tp3'] !== null ? number_format((float) $lm['tp3'], 2, '.', '') : '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            {{ $lm['tp4'] !== null ? number_format((float) $lm['tp4'], 2, '.', '') : '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-center font-semibold text-gray-800">
                                            {{ $lm['nilai'] !== null ? number_format((float) $lm['nilai'], 2, '.', '') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- RINGKASAN PER MAPEL --}}
                                <tr class="border-b-4 border-white bg-slate-50">
                                    <td colspan="5" class="px-4 py-3 text-right font-medium text-gray-600">
                                        Nilai Akhir / Status / Predikat
                                    </td>

                                    <td colspan="2" class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                Nilai Akhir:
                                                <span class="ml-1 font-bold">
                                                    {{ $row->nilai_akhir !== null ? number_format((float) $row->nilai_akhir, 2, '.', '') : '—' }}
                                                </span>
                                            </span>

                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                {{ ($row->status ?? '') === 'tuntas'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-rose-100 text-rose-700' }}">
                                                {{ $row->status ? ucfirst(str_replace('_', ' ', $row->status)) : '-' }}
                                            </span>

                                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                                Predikat {{ $row->predikat ?? '-' }}
                                            </span>
                                        </div>
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