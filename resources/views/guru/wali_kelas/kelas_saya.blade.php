@extends('layouts.guru')

@section('title', 'Kelas Saya')

@section('content')
@php
    $jkLabel = function ($jk) {
        $jk = strtoupper((string) $jk);

        return $jk === 'L'
            ? 'Laki-laki'
            : ($jk === 'P' ? 'Perempuan' : '-');
    };

    $rombels = $rombels ?? collect();
@endphp

<div class="space-y-6">

    @forelse($rombels as $r)
        @php
            $tahunAjaranLabel =
                $r->tahunAjaran->nama_tahun
                ?? $r->tahunAjaran->nama
                ?? $taAktif
                ?? '—';

            $listSiswa = $r->relationLoaded('siswa')
                ? $r->siswa
                : $r->siswa()
                    ->select('siswa.*')
                    ->wherePivot('aktif', 1)
                    ->orderBy('siswa.nama')
                    ->get();

            $jumlahSiswa = $listSiswa->count();
        @endphp

        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

            {{-- CARD HEADER --}}
            <div class="border-b bg-gray-50 px-6 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $r->nama_rombel ?? 'Kelas Saya' }}
                        </h2>

                        <div class="mt-2 flex flex-wrap gap-2">

                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Tahun Ajaran {{ $tahunAjaranLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-white px-5 py-3 text-center">
                        <p class="text-xs text-gray-500">Jumlah Siswa</p>
                        <p class="mt-1 text-2xl font-bold text-indigo-600">
                            {{ $jumlahSiswa }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- SEARCH --}}
            <div class="border-b px-6 py-4">
                <form id="searchKelasSayaForm" method="GET" action="{{ route('guru.wali.kelas-saya') }}">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Cari Siswa
                    </label>

                    <div class="relative max-w-xl">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
                            </svg>
                        </span>

                        <input type="text"
                               id="searchKelasSayaInput"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Cari nama, NIS, atau NISN siswa..."
                               autocomplete="off"
                               class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                        @if(request('q'))
                            <a href="{{ route('guru.wali.kelas-saya') }}"
                               class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-400 hover:text-red-500">
                                ✕
                            </a>
                        @endif
                    </div>

                    <p class="mt-2 text-xs text-gray-400">
                        Pencarian berjalan otomatis setelah mengetik.
                    </p>
                </form>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left">No</th>
                            <th class="px-5 py-3 text-left">NIS</th>
                            <th class="px-5 py-3 text-left">NISN</th>
                            <th class="px-5 py-3 text-left">Nama Siswa</th>
                            <th class="px-5 py-3 text-center">Jenis Kelamin</th>
                            <th class="px-5 py-3 text-left">Tempat/Tanggal Lahir</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($listSiswa as $index => $s)
                            @php
                                $jenisKelamin = $s->jk_label
                                    ?? $jkLabel($s->jk ?? $s->jenis_kelamin ?? null);

                                $tempatTanggalLahir = collect([
                                    $s->tempat_lahir ?? null,
                                    !empty($s->tanggal_lahir)
                                        ? \Illuminate\Support\Carbon::parse($s->tanggal_lahir)->translatedFormat('d M Y')
                                        : null,
                                ])->filter()->join(', ');

                                $detailId = 'detail-' . ($r->id ?? 'r') . '-' . ($s->id ?? $index);
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-5 py-4 font-medium text-gray-800">
                                    {{ $s->nis ?? '-' }}
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ $s->nisn ?? '-' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $s->nama ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $s->email ?? 'Email belum tersedia' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if(($s->jk ?? $s->jenis_kelamin ?? '') === 'L')
                                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            Laki-laki
                                        </span>
                                    @elseif(($s->jk ?? $s->jenis_kelamin ?? '') === 'P')
                                        <span class="inline-flex rounded-full border border-pink-200 bg-pink-50 px-2.5 py-1 text-xs font-semibold text-pink-700">
                                            Perempuan
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                            {{ $jenisKelamin }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ $tempatTanggalLahir ?: '-' }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <button type="button"
                                            onclick="toggleDetail('{{ $detailId }}')"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            {{-- DETAIL --}}
                            <tr id="{{ $detailId }}" class="hidden bg-gray-50">
                                <td colspan="7" class="px-5 py-5">
                                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                                        <div class="mb-4">
                                            <h3 class="text-base font-semibold text-gray-900">
                                                Detail Siswa
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Informasi identitas dan kontak siswa.
                                            </p>
                                        </div>

                                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">NIS</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->nis ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">NISN</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->nisn ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Jenis Kelamin</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $jenisKelamin }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Agama</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->agama ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Tempat Lahir</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->tempat_lahir ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Tanggal Lahir</p>
                                                <p class="mt-1 font-semibold text-gray-900">
                                                    {{ !empty($s->tanggal_lahir) ? \Illuminate\Support\Carbon::parse($s->tanggal_lahir)->translatedFormat('d M Y') : '-' }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">No HP</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->no_hp ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Email</p>
                                                <p class="mt-1 break-all font-semibold text-gray-900">{{ $s->email ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Status</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ ucfirst($s->status ?? '-') }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3 xl:col-span-3">
                                                <p class="text-xs text-gray-500">Alamat</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->alamat ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Nama Ayah</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->nama_ayah ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">No HP Ayah</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->no_hp_ayah ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">Nama Ibu</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->nama_ibu ?? '-' }}</p>
                                            </div>

                                            <div class="rounded-xl border bg-gray-50 px-4 py-3">
                                                <p class="text-xs text-gray-500">No HP Ibu</p>
                                                <p class="mt-1 font-semibold text-gray-900">{{ $s->no_hp_ibu ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">
                                    Tidak ada siswa yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                🏫
            </div>

            <h2 class="text-base font-semibold text-gray-900">
                Belum menjadi wali kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Akun guru Anda belum terhubung sebagai wali kelas pada rombel mana pun.
            </p>
        </div>
    @endforelse

</div>

<script>
    function toggleDetail(id) {
        const el = document.getElementById(id);
        if (!el) return;

        el.classList.toggle('hidden');
    }

    const searchKelasSayaInput = document.getElementById('searchKelasSayaInput');
    const searchKelasSayaForm = document.getElementById('searchKelasSayaForm');

    if (searchKelasSayaInput && searchKelasSayaForm) {
        let typingTimer;

        searchKelasSayaInput.addEventListener('input', function () {
            clearTimeout(typingTimer);

            typingTimer = setTimeout(function () {
                searchKelasSayaForm.submit();
            }, 500);
        });
    }
</script>
@endsection