@extends('layouts.siswa')

@section('content')
@php
    $fmt = function ($value) {
        return $value !== null && $value !== ''
            ? number_format((float) $value, 2)
            : '-';
    };

    $fmtKkm = function ($value) {
        return $value !== null && $value !== '' && (float) $value > 0
            ? number_format((float) $value, 0)
            : '-';
    };
@endphp

<div class="mb-5">
    <a href="{{ route('siswa.nilai.index') }}"
       class="inline-flex items-center px-4 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">
        ← Kembali ke Nilai
    </a>
</div>

<div class="bg-white rounded-xl shadow border mb-5">
    <div class="px-5 py-4 border-b">
        <h1 class="text-xl font-semibold text-gray-900">
            Detail Nilai Semester
        </h1>

        <div class="mt-2 flex flex-wrap gap-2 text-sm">
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                Tahun Ajaran: <strong>{{ $tahunAjaran ?? '-' }}</strong>
            </span>

            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                Semester: <strong>{{ $semester ?? '-' }}</strong>
            </span>

            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                Tingkat: <strong>{{ $tingkat ?? '-' }}</strong>
            </span>

            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">
                Rata-rata: <strong>{{ $rataSemester !== null ? number_format($rataSemester, 2) : '-' }}</strong>
            </span>
        </div>
    </div>

    <div class="px-5 py-4 text-sm text-gray-600">
        Halaman ini menampilkan rincian nilai TP1–TP4 dan LM1–LM4 tanpa perlu mengunduh PDF.
    </div>
</div>

<div class="space-y-4">
    @forelse($rows as $i => $row)
        @php
            $lmRows = [
                [
                    'label' => 'LM1',
                    'tp1' => $row->lm1_tp1 ?? null,
                    'tp2' => $row->lm1_tp2 ?? null,
                    'tp3' => $row->lm1_tp3 ?? null,
                    'tp4' => $row->lm1_tp4 ?? null,
                    'nilai' => $row->lm1_nilai ?? null,
                ],
                [
                    'label' => 'LM2',
                    'tp1' => $row->lm2_tp1 ?? null,
                    'tp2' => $row->lm2_tp2 ?? null,
                    'tp3' => $row->lm2_tp3 ?? null,
                    'tp4' => $row->lm2_tp4 ?? null,
                    'nilai' => $row->lm2_nilai ?? null,
                ],
                [
                    'label' => 'LM3',
                    'tp1' => $row->lm3_tp1 ?? null,
                    'tp2' => $row->lm3_tp2 ?? null,
                    'tp3' => $row->lm3_tp3 ?? null,
                    'tp4' => $row->lm3_tp4 ?? null,
                    'nilai' => $row->lm3_nilai ?? null,
                ],
                [
                    'label' => 'LM4',
                    'tp1' => $row->lm4_tp1 ?? null,
                    'tp2' => $row->lm4_tp2 ?? null,
                    'tp3' => $row->lm4_tp3 ?? null,
                    'tp4' => $row->lm4_tp4 ?? null,
                    'nilai' => $row->lm4_nilai ?? null,
                ],
            ];
        @endphp

        <details class="bg-white rounded-xl shadow border overflow-hidden" {{ $i === 0 ? 'open' : '' }}>
            <summary class="cursor-pointer px-5 py-4 bg-gray-50 hover:bg-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">
                            Mata Pelajaran {{ $i + 1 }}
                        </div>

                        <h2 class="font-semibold text-gray-900">
                            {{ $row->mapel ?? '-' }}
                        </h2>

                        <p class="text-sm text-gray-600 mt-1">
                            Guru: {{ $row->guru ?? '-' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            Nilai Akhir: {{ $fmt($row->nilai_akhir ?? null) }}
                        </span>

                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                            KKM: {{ $fmtKkm($row->kkm ?? null) }}
                        </span>

                        @if(($row->status_penilaian ?? null) === 'final')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                                Final
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                Sementara
                            </span>
                        @endif

                        @if(($row->status ?? null) === 'tuntas')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                                Tuntas
                            </span>
                        @elseif(($row->status ?? null) === 'tidak_tuntas')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                Tidak Tuntas
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-100">
                                Belum Dinilai
                            </span>
                        @endif
                    </div>
                </div>
            </summary>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white border-b text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Lingkup Materi</th>
                            <th class="px-4 py-3 text-center">TP1</th>
                            <th class="px-4 py-3 text-center">TP2</th>
                            <th class="px-4 py-3 text-center">TP3</th>
                            <th class="px-4 py-3 text-center">TP4</th>
                            <th class="px-4 py-3 text-center bg-indigo-50 text-indigo-700">Nilai LM</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach($lmRows as $lm)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    {{ $lm['label'] }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ $fmt($lm['tp1']) }}</td>
                                <td class="px-4 py-3 text-center">{{ $fmt($lm['tp2']) }}</td>
                                <td class="px-4 py-3 text-center">{{ $fmt($lm['tp3']) }}</td>
                                <td class="px-4 py-3 text-center">{{ $fmt($lm['tp4']) }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-indigo-700 bg-indigo-50">
                                    {{ $fmt($lm['nilai']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="bg-gray-50 border-t">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">
                                Nilai Akhir
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-indigo-700">
                                {{ $fmt($row->nilai_akhir ?? null) }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">
                                KKM
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-amber-700">
                                {{ $fmtKkm($row->kkm ?? null) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </details>
    @empty
        <div class="bg-white rounded-xl shadow border px-5 py-8 text-center text-gray-500">
            Tidak ada data nilai pada semester ini.
        </div>
    @endforelse
</div>
@endsection