@extends('layouts.admin')

@section('content')
@php
    $taDipilihLabel = $taDipilih
        ? (($taDipilih->nama_tahun ?? $taDipilih->nama ?? $taDipilih->label ?? '—') . ' / ' . ($taDipilih->semester ?? '—'))
        : 'Belum diatur';

    $rombelId = $rombel_id ?? '';
    $status = $statusFinal ?? '';
    $isAll = empty($rombelId);

    $baseParams = array_filter([
        'tahun_ajaran_id' => $tahunAjaranId ?? request('tahun_ajaran_id'),
        'q' => $q ?? '',
        'status_final' => $status ?? '',
    ], function ($value) {
        return $value !== null && $value !== '';
    });

    $namaTahunDipilih = $taDipilih->nama_tahun
        ?? $taDipilih->nama
        ?? $taDipilih->label
        ?? '-';
@endphp

<div class="space-y-5">
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Monitoring Penilaian
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Pantau progres input nilai per kelas, mapel, dan guru berdasarkan tahun ajaran.
            </p>

            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
                Semester: {{ $taDipilihLabel }}
                @if(($taDipilih->status ?? null) === 'aktif')
                    <span class="font-semibold"></span>
                @endif
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.penilaian.index') }}" class="space-y-4">
            <input type="hidden" name="rombel_id" value="{{ $rombelId }}">

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <label class="mb-1 block text-sm font-semibold text-gray-700">
                        Tahun Ajaran
                    </label>

                    <select name="tahun_ajaran_id"
                            class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($daftarTahunAjaran as $ta)
                            @php
                                $labelTa = $ta->nama_tahun
                                    ?? $ta->nama
                                    ?? $ta->label
                                    ?? '-';
                            @endphp

                            <option value="{{ $ta->id }}" @selected((int) $tahunAjaranId === (int) $ta->id)>
                                {{ $labelTa }}
                                @if(($ta->status ?? null) === 'aktif')
                                    — Aktif
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-5">
                    <label class="mb-1 block text-sm font-semibold text-gray-700">
                        Pencarian
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 2a8 8 0 105.293 14.293l3.707 3.707a1 1 0 001.414-1.414l-3.707-3.707A8 8 0 0010 2zm-6 8a6 6 0 1112 0 6 6 0 01-12 0z"/>
                            </svg>
                        </span>

                        <input type="text"
                               name="q"
                               value="{{ $q ?? '' }}"
                               placeholder="Cari kelas, mapel, atau guru..."
                               class="w-full rounded-xl border-gray-300 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-gray-700">
                        Status
                    </label>

                    <select name="status_final"
                            class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="final" {{ $status === 'final' ? 'selected' : '' }}>Final</option>
                    </select>
                </div>

                <div class="flex items-end gap-3 lg:col-span-2">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        Terapkan
                    </button>

                    <a href="{{ route('admin.penilaian.index') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>

        </form>
    </div>

    {{-- TAB KELAS --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900">
                Kelas
            </h2>

            <p class="text-xs text-gray-500">
                Pilih kelas untuk melihat daftar mapel dan status input nilai pada tahun ajaran
                <span class="font-semibold text-gray-700">{{ $namaTahunDipilih }}</span>.
            </p>
        </div>

        <div class="overflow-x-auto px-3 py-3">
            <div class="flex min-w-max gap-2">
                <a href="{{ route('admin.penilaian.index', $baseParams) }}"
                   class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                   {{ $isAll ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                    Semua
                </a>

                @forelse(($rombels ?? []) as $r)
                    @php
                        $id = $r->id;
                        $nama = $r->nama_rombel ?? $r->nama ?? ('Kelas '.$id);
                        $active = (string)$rombelId === (string)$id;
                    @endphp

                    <a href="{{ route('admin.penilaian.index', array_merge($baseParams, ['rombel_id' => $id])) }}"
                       class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition
                       {{ $active ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        {{ $nama }}
                    </a>
                @empty
                    <span class="rounded-xl border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-500">
                        Belum ada rombel pada tahun ajaran ini
                    </span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm text-gray-800">
                <thead class="border-b bg-gray-50">
                    <tr>
                        @if($isAll)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Kelas
                            </th>
                        @endif

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Mapel
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Guru
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Progress Nilai
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Rata-rata
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $row)
                        @php
                            $kelas = $row->rombel->nama_rombel ?? $row->rombel->nama ?? '-';
                            $mapel = $row->mataPelajaran->nama_mapel ?? $row->mataPelajaran->nama ?? '-';
                            $guru  = $row->guru->nama ?? '-';

                            $total = (int)($row->siswa_total ?? 0);

                            $progress = array_merge(
                                ['lm1'=>0,'lm2'=>0,'lm3'=>0,'lm4'=>0],
                                (array)($row->progress ?? [])
                            );

                            $final = (($row->status_final ?? 'draft') === 'final');
                        @endphp

                        <tr class="hover:bg-gray-50">
                            @if($isAll)
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $kelas }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-400">
                                        {{ $row->rombel->tahunAjaran->nama_tahun ?? $row->rombel->tahunAjaran->nama ?? '' }}
                                    </div>
                                </td>
                            @endif

                            <td class="whitespace-nowrap px-5 py-4">
                                {{ $mapel }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                {{ $guru }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-lg bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                        LM1: {{ $progress['lm1'] }}/{{ $total }}
                                    </span>

                                    <span class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                        LM2: {{ $progress['lm2'] }}/{{ $total }}
                                    </span>

                                    <span class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                        LM3: {{ $progress['lm3'] }}/{{ $total }}
                                    </span>

                                    <span class="rounded-lg bg-fuchsia-50 px-2 py-1 text-xs font-medium text-fuchsia-700">
                                        LM4: {{ $progress['lm4'] }}/{{ $total }}
                                    </span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if ($final)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        FINAL
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        DRAFT
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if($row->avg !== null)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                        {{ number_format($row->avg, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                              <a href="{{ route('admin.penilaian.show', ['jadwal' => $row->id, 'tahun_ajaran_id' => $tahunAjaranId]) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                  Lihat Detail
                              </a>                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAll ? 7 : 6 }}" class="px-5 py-10 text-center text-gray-500">
                                Belum ada data monitoring penilaian pada tahun ajaran ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div class="border-t bg-gray-50 px-5 py-4">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection