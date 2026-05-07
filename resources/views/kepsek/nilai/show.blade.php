@extends('layouts.kepsek')

@section('content')
@php
    $num = function ($v) {
        return $v !== null ? number_format((float) $v, 2) : '—';
    };

    $tahunLabel = $tahunLabel
        ?? ($tahunDipilih->nama_tahun ?? $tahunDipilih->tahun ?? '-');

    $semester = $semester ?? request('semester', 'Ganjil');

    $routeIndex = route('kepala_sekolah.monitor.nilai.index', [
        'tahun_ajaran_id' => $tahunAjaranId ?? null,
        'semester' => $semester,
        'rombel_id' => $jadwal->rombel_id ?? null,
    ]);

    $isTahunAktif = isset($taAktifModel, $tahunAjaranId)
        && (int) $taAktifModel->id === (int) $tahunAjaranId;
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Monitoring Penilaian
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Pantau hasil penilaian siswa per mata pelajaran dan semester.
            </p>
        </div>

        <a href="{{ $routeIndex }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Kembali
        </a>
    </div>

    {{-- INFORMASI --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Informasi Penilaian
            </h2>
        </div>

        <div class="px-5 py-4">
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                    Kelas:
                    <strong class="text-gray-900">{{ $rombel }}</strong>
                </span>

                <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">
                    Mata Pelajaran:
                    <strong>{{ $mapel }}</strong>
                </span>

                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                    Guru:
                    <strong class="text-gray-900">{{ $guruNama }}</strong>
                </span>

            </div>

<div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Tahun Ajaran
            </div>
            <div class="mt-1 font-semibold text-gray-900">
                {{ $tahunLabel }}
            </div>
        </div>

        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Semester
            </div>
            <div class="mt-1 font-semibold text-gray-900">
                {{ $semester }}
            </div>
        </div>
    </div>
</div>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">
                Daftar Nilai Siswa
            </h2>
            <p class="mt-1 text-xs text-gray-500">
                Menampilkan ringkasan nilai LM1 sampai LM4, nilai akhir, status ketuntasan, dan status finalisasi.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-indigo-600 text-white">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">
                            No
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">
                            NIS
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">
                            Nama Siswa
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            LM1
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            LM2
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            LM3
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            LM4
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            Nilai Akhir
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            Status
                        </th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                            Finalisasi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($siswa as $i => $s)
                        @php
                            $row = $nilai->get($s->id);
                            $isFinal = ($row->status_penilaian ?? 'draft') === 'final';
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $i + 1 }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $s->nis ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $s->nama ?? '-' }}
                                </div>
                                @if(!empty($s->nisn))
                                    <div class="mt-1 text-xs text-gray-400">
                                        NISN: {{ $s->nisn }}
                                    </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                {{ $num($row->lm1_nilai ?? null) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                {{ $num($row->lm2_nilai ?? null) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                {{ $num($row->lm3_nilai ?? null) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                {{ $num($row->lm4_nilai ?? null) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center font-semibold text-gray-900">
                                {{ $num($row->nilai_akhir ?? null) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if(($row->status ?? null) === 'tuntas')
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        Tuntas
                                    </span>
                                @elseif(($row->status ?? null) === 'tidak_tuntas')
                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                        Tidak Tuntas
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if($isFinal)
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        Final
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Draft
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    Tidak ada data nilai siswa
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Belum ada nilai untuk tahun ajaran {{ $tahunLabel }} semester {{ $semester }}.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection