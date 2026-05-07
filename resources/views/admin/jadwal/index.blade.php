@extends('layouts.admin')

@section('content')
@php
    $items = $items ?? collect();
    $rombelId = request('rombel_id');
    $tahunAjaranId = $tahunAjaranId ?? request('tahun_ajaran_id');
    $startNo = method_exists($items, 'firstItem') ? $items->firstItem() : 1;

    $baseParams = array_filter(request()->only(['tahun_ajaran_id', 'guru_id', 'hari', 'q']), function ($value) {
        return $value !== null && $value !== '';
    });

    $hariOptions = $hariOptions ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $tahunDipilih = collect($daftarTahunAjaran ?? [])->firstWhere('id', (int) $tahunAjaranId);
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Jadwal</h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola jadwal pembelajaran berdasarkan tahun ajaran, kelas, guru, mata pelajaran, hari, dan jam.
            </p>
        </div>

        <a href="{{ route('admin.jadwal.create', array_filter(['tahun_ajaran_id' => $tahunAjaranId])) }}"
           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            + Tambah Jadwal
        </a>
    </div>

    {{-- Alert --}}
    @if(session('ok'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('err'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('err') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form id="filterJadwalForm" method="GET" action="{{ route('admin.jadwal.index') }}">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($daftarTahunAjaran as $ta)
                            @php
                                $labelTa = $ta->nama_tahun ?? $ta->nama ?? $ta->label ?? '-';
                                $isAktif = ($ta->status ?? null) === 'aktif';
                            @endphp
                            <option value="{{ $ta->id }}" @selected((int) $tahunAjaranId === (int) $ta->id)>
                                {{ $labelTa }}{{ $isAktif ? ' — Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Rombel</label>
                    <select name="rombel_id"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Rombel</option>
                        @foreach($daftarRombel as $r)
                            <option value="{{ $r->id }}" @selected(request('rombel_id') == $r->id)>
                                {{ $r->nama_rombel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Guru</label>
                    <select name="guru_id"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Guru</option>
                        @foreach($daftarGuru as $g)
                            <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>
                                {{ $g->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Hari</label>
                    <select name="hari"
                            class="auto-filter w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Hari</option>
                        @foreach($hariOptions as $h)
                            <option value="{{ $h }}" @selected(request('hari') == $h)>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Pencarian</label>
                    <div class="relative">
                        <input type="text"
                               id="searchJadwalInput"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Cari kelas, mapel, guru, atau hari..."
                               autocomplete="off"
                               class="w-full rounded-xl border-gray-300 pr-10 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                        @if(request('q'))
                            <a href="{{ route('admin.jadwal.index', request()->except('q')) }}"
                               class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-400 hover:text-red-500">
                                ✕
                            </a>
                        @endif
                    </div>
                </div>

            </div>

            <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-xs text-gray-400">
                    Default menampilkan jadwal pada tahun ajaran aktif. Gunakan filter tahun ajaran untuk melihat histori jadwal.
                </p>

                <a href="{{ route('admin.jadwal.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabs Kelas --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b bg-gray-50 px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-800">Kelas</h2>
            <p class="mt-1 text-xs text-gray-500">
                Pilih kelas untuk melihat jadwal pada tahun ajaran
                <span class="font-semibold text-gray-700">
                    {{ $tahunDipilih->nama_tahun ?? $tahunDipilih->nama ?? $tahunDipilih->label ?? '-' }}
                </span>.
            </p>
        </div>

        <div class="overflow-x-auto px-4 py-3">
            <div class="flex min-w-max gap-2">
                <a href="{{ route('admin.jadwal.index', $baseParams) }}"
                   class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                   {{ empty($rombelId) ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                    Semua
                </a>

                @forelse($daftarRombel as $r)
                    <a href="{{ route('admin.jadwal.index', array_merge($baseParams, ['rombel_id' => $r->id])) }}"
                       class="rounded-xl border px-4 py-2 text-sm font-semibold transition
                       {{ (string)$rombelId === (string)$r->id ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        {{ $r->nama_rombel }}
                    </a>
                @empty
                    <span class="rounded-xl border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-500">
                        Belum ada rombel pada tahun ajaran ini
                    </span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">No</th>

                        @if(empty($rombelId))
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kelas</th>
                        @endif

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mapel</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Guru</th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Hari</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jam</th>
                        <th class="sticky right-0 whitespace-nowrap bg-gray-50 px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $row)
                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $startNo + $loop->index }}
                            </td>

                            @if(empty($rombelId))
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $row->rombel->nama_rombel ?? '—' }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-400">
                                        {{ $row->rombel->tahunAjaran->nama_tahun ?? $row->rombel->tahunAjaran->nama ?? '' }}
                                    </div>
                                </td>
                            @endif

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $row->mataPelajaran->nama_mapel ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $row->guru->nama ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $row->hari ?? '—' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                {{ $row->jam_mulai ? substr($row->jam_mulai, 0, 5) : '--:--' }}
                                -
                                {{ $row->jam_selesai ? substr($row->jam_selesai, 0, 5) : '--:--' }}
                            </td>

                            <td class="sticky right-0 bg-white px-5 py-4 min-w-[170px]">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('admin.jadwal.show', $row) }}"
                                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.jadwal.edit', $row) }}"
                                       class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                        Ubah
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ empty($rombelId) ? 7 : 6 }}" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">Data jadwal tidak ditemukan</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Data jadwal belum tersedia atau tidak sesuai filter tahun ajaran yang dipilih.
                                </p>
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

<script>
    const filterJadwalForm = document.getElementById('filterJadwalForm');
    const autoFilters = document.querySelectorAll('.auto-filter');
    const searchInput = document.getElementById('searchJadwalInput');

    if (filterJadwalForm && autoFilters.length) {
        autoFilters.forEach((filter) => {
            filter.addEventListener('change', function () {
                /*
                 * Kalau tahun ajaran berubah, rombel_id lama bisa tidak valid
                 * untuk tahun ajaran baru. Jadi rombel_id dikosongkan dulu.
                 */
                if (filter.name === 'tahun_ajaran_id') {
                    const rombelSelect = filterJadwalForm.querySelector('select[name="rombel_id"]');
                    if (rombelSelect) {
                        rombelSelect.value = '';
                    }
                }

                filterJadwalForm.submit();
            });
        });
    }

    if (filterJadwalForm && searchInput) {
        let typingTimer;

        searchInput.addEventListener('input', function () {
            clearTimeout(typingTimer);

            typingTimer = setTimeout(function () {
                filterJadwalForm.submit();
            }, 500);
        });
    }
</script>
@endsection