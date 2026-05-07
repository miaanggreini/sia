{{-- resources/views/guru/ekskul/anggota.blade.php --}}
@extends('layouts.guru')

@section('title','Anggota Ekskul')

@section('content')
  {{-- HEADER --}}
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-semibold">Anggota Ekskul</h1>
      <p class="text-sm text-gray-500">
        Ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
      </p>
    </div>

    <a href="{{ route('guru.ekskul.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">

      Kembali
    </a>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- CARD UTAMA --}}
  <div class="rounded-2xl border bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-4">
      {{-- PILIH TAHUN AJARAN --}}
      <div class="flex items-center gap-2 text-sm">
        <span class="text-xs text-gray-500">Tahun ajaran</span>
        <form method="GET" class="flex items-center gap-2">
          <select name="tahun_ajaran_id"
                  class="h-9 rounded-lg border-gray-300 text-sm">
            @foreach ($tahunList as $ta)
              <option value="{{ $ta->id }}"
                      @selected(optional($tahunDipilih)->id === $ta->id)>
                {{ $ta->nama_tahun ?? $ta->nama }}
                @if($ta->status === 'aktif') (aktif) @endif
              </option>
            @endforeach
          </select>
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
            Terapkan
          </button>
        </form>
      </div>

      {{-- TOTAL ANGGOTA --}}
      <div class="text-xs text-gray-500">
        Total anggota:
        <span class="font-semibold text-gray-800">{{ $anggota->count() }}</span>
      </div>
    </div>

    {{-- TABEL ANGGOTA --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <th class="px-4 py-2 text-left">No</th>
            <th class="px-4 py-2 text-left">NIS / NISN</th>
            <th class="px-4 py-2 text-left">Nama Siswa</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Tgl Gabung</th>
            <th class="px-4 py-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($anggota as $idx => $row)
            <tr class="hover:bg-gray-50/60">
              {{-- NO --}}
              <td class="px-4 py-2 align-middle text-xs text-gray-500">
                {{ $idx + 1 }}
              </td>

              {{-- NIS / NISN --}}
              <td class="px-4 py-2 align-middle">
                <div class="text-sm text-gray-800">
                  {{ $row->siswa->nis ?? '-' }}
                </div>
                <div class="text-[11px] text-gray-400">
                  NISN: {{ $row->siswa->nisn ?? '-' }}
                </div>
              </td>

              {{-- NAMA SISWA --}}
              <td class="px-4 py-2 align-middle text-sm text-gray-900">
                {{ $row->siswa->nama ?? '-' }}
              </td>

              {{-- STATUS --}}
              <td class="px-4 py-2 align-middle">
                @if($row->status === 'aktif')
                  <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700">
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-medium text-gray-600">
                    Nonaktif
                  </span>
                @endif
              </td>

              {{-- TGL GABUNG --}}
              <td class="px-4 py-2 align-middle text-sm text-gray-700">
                  {{ $row->tanggal_gabung 
                      ? \Carbon\Carbon::parse($row->tanggal_gabung)->translatedFormat('d M Y') 
                      : '-' 
                  }}
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-2 text-right align-middle">
                @if($row->status === 'aktif')
                  {{-- Modal per-baris pakai Alpine --}}
                  <div x-data="{ open: false }" class="inline-block">
                    <button type="button"
                            @click="open = true"
                            class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-[11px] font-medium text-rose-700 hover:bg-rose-100">
                      Keluarkan
                    </button>

                    {{-- BACKDROP + MODAL --}}
                    <div x-show="open"
                         x-cloak
                         class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
                      <div @click.away="open = false"
                           class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl">
                        <h2 class="mb-2 text-base font-semibold text-gray-900">
                          Keluarkan siswa dari ekskul?
                        </h2>
                        <p class="mb-4 text-sm text-gray-600">
                          Keluarkan
                          <span class="font-semibold">{{ $row->siswa->nama ?? '-' }}</span>
                          dari ekskul
                          <span class="font-semibold">{{ $ekskul->nama }}</span>?
                        </p>

                        <div class="flex justify-end gap-2 text-sm">
                          <button type="button"
                                  @click="open = false"
                                  class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">
                            Batal
                          </button>

                          <form method="POST"
                                action="{{ route('guru.ekskul.anggota.keluarkan', [$ekskul, $row]) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-md bg-rose-600 px-3 py-1.5 text-white hover:bg-rose-700">
                              Ya, keluarkan
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                @else
                  <span class="text-[11px] text-gray-400">-</span>
                @endif
              </td>
            </tr>
          @empty
    <tr>
      <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
        Belum ada anggota ekskul untuk tahun ajaran ini.
      </td>
    </tr>
@endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
