{{-- resources/views/admin/menu_rombel/index.blade.php --}}
@extends('layouts.admin')
@section('title','Menu Rombel')

@section('content')
@php
  $q = $q ?? request('q');
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Menu Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola menu pilihan rombel untuk tahun ajaran
        <span class="font-semibold text-gray-700">{{ $taLabel ?? '—' }}</span>.
      </p>
    </div>

    <a href="{{ route('admin.menu-rombel.create') }}"
       class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
      <span class="text-lg leading-none">+</span>
      Buat Menu
    </a>
  </div>

{{-- Filter & Search --}}
<form method="GET" class="rounded-2xl border bg-white p-4 shadow-sm">
  <div class="grid grid-cols-1 gap-3 md:grid-cols-3 md:items-center">

    <div>
      <select name="periode_id"
              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        <option value="">Semua Periode</option>
        @foreach($periodeList as $periode)
          <option value="{{ $periode->id }}" @selected((string)($periodeId ?? '') === (string)$periode->id)>
            {{ $periode->nama_periode ?? 'Periode '.$periode->id }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <input
        type="text"
        name="q"
        value="{{ $q }}"
        placeholder="Cari nama menu atau mata pelajaran..."
        class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
      >
    </div>

    <div class="flex gap-2">
      <button type="submit"
              class="flex-1 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition">
        Terapkan
      </button>

      @if($q || $periodeId)
        <a href="{{ route('admin.menu-rombel.index') }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Reset
        </a>
      @endif
    </div>
  </div>

  @if($periodeAktif)
    <div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
      Menampilkan menu yang digunakan pada
      <span class="font-semibold">{{ $periodeAktif->nama_periode ?? 'periode terpilih' }}</span>.
    </div>
  @endif
</form>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-800">Daftar Menu Rombel</h2>
      <p class="mt-1 text-sm text-gray-500">
        Setiap menu berisi kelompok mata pelajaran pilihan dan kapasitas siswa.
      </p>
    </div>

    @if($items->isEmpty())
      <div class="px-5 py-12 text-center">
        <div class="text-base font-semibold text-gray-700">Belum ada menu rombel</div>
        <p class="mt-1 text-sm text-gray-500">
          Buat menu rombel terlebih dahulu agar siswa dapat memilih menu sesuai minatnya.
        </p>

        <a href="{{ route('admin.menu-rombel.create') }}"
           class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
          Buat Menu
        </a>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
              <th class="px-5 py-3 text-left">Nama Menu</th>
              <th class="px-5 py-3 text-center">Tingkat</th>
              <th class="px-5 py-3 text-center">Kapasitas</th>
              <th class="px-5 py-3 text-left">Mata Pelajaran</th>
              <th class="px-5 py-3 text-center">Status</th>
              <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach($items as $m)
              <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4 align-top">
                  <div class="font-semibold text-gray-800">{{ $m->nama }}</div>
                  <div class="mt-1 text-xs text-gray-500">
                    TA: {{ $m->tahunAjaran->label ?? $m->tahunAjaran->nama_tahun ?? ($taLabel ?? '—') }}
                  </div>
                </td>

                <td class="px-5 py-4 text-center align-top">
                  <span class="inline-flex rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    {{ $m->tingkat ?? 'XI' }}
                  </span>
                </td>

                <td class="px-5 py-4 text-center align-top">
                  <span class="inline-flex rounded-full border border-green-100 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                    {{ number_format($m->kapasitas_total) }}
                  </span>
                </td>

                <td class="px-5 py-4 align-top">
                  <div class="flex max-w-3xl flex-wrap gap-1.5">
                    @forelse($m->mapel as $mp)
                      <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700">
                        {{ $mp->nama_mapel }}
                      </span>
                    @empty
                      <span class="text-xs text-gray-400">Belum ada mapel</span>
                    @endforelse
                  </div>
                </td>

                <td class="px-5 py-4 text-center align-top">
                  @if($m->aktif ?? true)
                    <span class="inline-flex items-center gap-1 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                      <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                      Aktif
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                      <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                      Nonaktif
                    </span>
                  @endif
                </td>

                <td class="px-5 py-4 align-top">
                  <div class="flex flex-wrap justify-end gap-2">
                    <a href="{{ route('admin.menu-rombel.edit', $m) }}"
                       class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                      Ubah
                    </a>

                    <form action="{{ route('admin.menu-rombel.destroy', $m) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus menu {{ $m->nama }}? Mapel yang terhubung akan terlepas.')">
                      @csrf
                      @method('DELETE')
                    </form>
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
@endsection