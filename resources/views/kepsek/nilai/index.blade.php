@extends('layouts.kepsek')

@section('content')
@php
    $items = $items ?? collect();

    $tahunLabel = $tahunLabel
        ?? ($tahunDipilih->nama_tahun ?? $tahunDipilih->tahun ?? '-');

    $semester = $semester ?? request('semester', 'Ganjil');

    $selectedParams = request()->except(['page', 'rombel_id']);

    $baseParams = [
        'tahun_ajaran_id' => $tahunAjaranId,
        'semester' => $semester,
        'q' => $q ?? null,
        'status_final' => $statusFinal ?? null,
    ];

    $baseParams = array_filter($baseParams, fn ($value) => $value !== null && $value !== '');

    $isAll = empty($rombel_id);

    $taAktifId = $taAktif->id ?? null;
    $isTahunAktif = $taAktifId && (int) $tahunAjaranId === (int) $taAktifId;

    $routeIndex = route('kepala_sekolah.monitor.nilai.index');

    $buildQuery = function (array $override = []) use ($baseParams) {
        return array_merge($baseParams, $override);
    };
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Monitoring Penilaian
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Pantau progres input nilai per kelas, mapel, dan guru berdasarkan tahun ajaran.
        </p>

        <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
            Semester: {{ $tahunLabel }} / {{ $semester }}
            @if($isTahunAktif)
                <span></span>
            @else
                <span class="text-amber-600"></span>
            @endif
        </div>
    </div>

    @if(session('ok'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('err'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('err') }}
        </div>
    @endif

    {{-- FILTER --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form id="filterNilaiForm" method="GET" action="{{ $routeIndex }}">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Tahun Ajaran
                    </label>
                    <select name="tahun_ajaran_id"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($daftarTahunAjaran as $ta)
                            @php
                                $labelTa = $ta->nama_tahun ?? $ta->tahun ?? '-';
                                $isAktifOption = $taAktif && (int) $ta->id === (int) $taAktif->id;
                            @endphp

                            <option value="{{ $ta->id }}" @selected((int) $tahunAjaranId === (int) $ta->id)>
                                {{ $labelTa }}{{ $isAktifOption ? ' — Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Semester
                    </label>
                    <select name="semester"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="Ganjil" @selected($semester === 'Ganjil')>Ganjil</option>
                        <option value="Genap" @selected($semester === 'Genap')>Genap</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Pencarian
                    </label>
                    <input type="text"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Cari kelas, mapel, atau guru..."
                           class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Status
                    </label>
                    <select name="status_final"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        <option value="draft" @selected(($statusFinal ?? '') === 'draft')>Draft</option>
                        <option value="final" @selected(($statusFinal ?? '') === 'final')>Final</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Terapkan
                    </button>

                    <a href="{{ $routeIndex }}"
                       class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB KELAS --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Kelas</h2>
            <p class="mt-1 text-sm text-gray-500">
                Pilih kelas untuk melihat daftar mapel dan status input nilai pada tahun ajaran
                <span class="font-semibold text-gray-700">{{ $tahunLabel }}</span>.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 px-5 py-4">
            <a href="{{ route('kepala_sekolah.monitor.nilai.index', $baseParams) }}"
               class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                    {{ $isAll
                        ? 'border-indigo-600 bg-indigo-600 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                Semua
            </a>

            @forelse(($rombels ?? collect()) as $r)
                @php
                    $active = (string)($rombel_id ?? '') === (string)$r->id;
                @endphp

                <a href="{{ route('kepala_sekolah.monitor.nilai.index', array_merge($baseParams, ['rombel_id' => $r->id])) }}"
                   class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                        {{ $active
                            ? 'border-indigo-600 bg-indigo-600 text-white'
                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                    {{ $r->nama_rombel ?? '-' }}
                </a>
            @empty
                <div class="text-sm text-gray-500">
                    Belum ada rombel pada tahun ajaran ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        @if($isAll)
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Kelas
                            </th>
                        @endif

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Mapel
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Guru
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Progress Nilai
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Rata-rata
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($items as $row)
                        @php
                            $kelas = $row->rombel->nama_rombel ?? '-';
                            $mapel = $row->mataPelajaran->nama_mapel ?? '-';
                            $guru  = $row->guru->nama ?? '-';

                            $total = (int)($row->siswa_total ?? 0);
                            $progress = array_merge(['lm1'=>0,'lm2'=>0,'lm3'=>0,'lm4'=>0], (array)($row->progress ?? []));
                            $final = (($row->status_final ?? 'draft') === 'final');
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            @if($isAll)
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $kelas }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        {{ $row->rombel->tahunAjaran->nama_tahun ?? $tahunLabel }}
                                    </div>
                                </td>
                            @endif

                            <td class="whitespace-nowrap px-5 py-4 text-gray-800">
                                {{ $mapel }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-800">
                                {{ $guru }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        LM1: {{ $progress['lm1'] }}/{{ $total }}
                                    </span>
                                    <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        LM2: {{ $progress['lm2'] }}/{{ $total }}
                                    </span>
                                    <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        LM3: {{ $progress['lm3'] }}/{{ $total }}
                                    </span>
                                    <span class="rounded-lg bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700">
                                        LM4: {{ $progress['lm4'] }}/{{ $total }}
                                    </span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if ($final)
                                    <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        FINAL
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700">
                                        DRAFT
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if($row->avg !== null)
                                    <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ number_format($row->avg, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <a href="{{ route('kepala_sekolah.monitor.nilai.show', [
                                        'jadwal' => $row->id,
                                        'tahun_ajaran_id' => $tahunAjaranId,
                                        'semester' => $semester,
                                    ]) }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAll ? 7 : 6 }}" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">Belum ada data monitoring nilai</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Data tidak ditemukan pada tahun ajaran {{ $tahunLabel }} semester {{ $semester }}.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const filterNilaiForm = document.getElementById('filterNilaiForm');
    const autoFilters = document.querySelectorAll('.auto-filter');

    if (filterNilaiForm && autoFilters.length) {
        autoFilters.forEach((filter) => {
            filter.addEventListener('change', function () {
                filterNilaiForm.submit();
            });
        });
    }
</script>
@endsection