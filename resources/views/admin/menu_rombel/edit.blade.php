{{-- resources/views/admin/menu_rombel/edit.blade.php --}}
@extends('layouts.admin')
@section('title','Ubah Menu Rombel')

@section('content')
@php
  $selected = old()
      ? collect(old('daftar_mapel', []))->map(fn($v) => (int) $v)->all()
      : $item->mapel->pluck('id')->map(fn($v) => (int) $v)->all();
@endphp

<div class="space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Ubah Menu Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Perbarui nama menu, kapasitas, status, dan mata pelajaran yang termasuk dalam menu ini.
      </p>
    </div>

    <a href="{{ route('admin.menu-rombel.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
      Kembali
    </a>
  </div>

  @if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      <div class="font-semibold">Data belum valid</div>
      <ul class="mt-2 list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ route('admin.menu-rombel.update', $item) }}"
        class="space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <h2 class="text-base font-semibold text-gray-800">Informasi Menu</h2>
        <p class="mt-1 text-sm text-gray-500">
          Ubah informasi dasar menu rombel yang akan digunakan siswa saat memilih.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Nama Menu
          </label>
          <input
            type="text"
            name="nama"
            value="{{ old('nama', $item->nama) }}"
            maxlength="100"
            placeholder="Contoh: Menu MIPA"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('nama') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>
          @error('nama')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
            <p class="mt-1 text-xs text-gray-500">
              Nama menu akan tampil pada pilihan siswa.
            </p>
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Kapasitas Total
          </label>
          <input
            type="number"
            name="kapasitas_total"
            min="1"
            value="{{ old('kapasitas_total', $item->kapasitas_total) }}"
            placeholder="Contoh: 50"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('kapasitas_total') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>
          @error('kapasitas_total')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
            <p class="mt-1 text-xs text-gray-500">
              Total kursi untuk seluruh kelas menu ini.
            </p>
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Status Menu
          </label>

          <select
            name="aktif"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('aktif') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>
            <option value="1" @selected(old('aktif', (string) $item->aktif) == '1')>Aktif</option>
            <option value="0" @selected(old('aktif', (string) $item->aktif) == '0')>Nonaktif</option>
          </select>

          @error('aktif')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
            <p class="mt-1 text-xs text-gray-500">
              Nonaktif berarti tidak muncul pada pilihan siswa.
            </p>
          @enderror
        </div>
      </div>
    </div>

    <div class="rounded-2xl border bg-white shadow-sm" x-data="mapelSelect({{ count($selected) }})">
      <div class="flex flex-col gap-3 border-b px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-800">Mata Pelajaran Menu</h2>
          <p class="mt-1 text-sm text-gray-500">
            Pilih mata pelajaran yang termasuk dalam menu rombel ini.
          </p>
        </div>

        <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
          Dipilih: <span x-text="dipilih"></span>
        </div>
      </div>

      <div class="p-5">
        @if($daftarMapel->isEmpty())
          <div class="rounded-xl border border-dashed p-8 text-center text-sm text-gray-500">
            Belum ada data mata pelajaran aktif.
          </div>
        @else
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($daftarMapel as $m)
              @php $checked = in_array((int) $m->id, $selected, true); @endphp

              <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm hover:border-blue-300 hover:bg-blue-50 transition">
                <input
                  type="checkbox"
                  name="daftar_mapel[]"
                  value="{{ $m->id }}"
                  @checked($checked)
                  @change="hitung()"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="font-medium text-gray-700">{{ $m->nama_mapel }}</span>
              </label>
            @endforeach
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition"
              @click="pilihSemua()">
              Pilih Semua
            </button>

            <button
              type="button"
              class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
              @click="resetSemua()">
              Reset
            </button>
          </div>
        @endif
      </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <a href="{{ route('admin.menu-rombel.index') }}"
         class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Batal
      </a>

      <button
        type="submit"
        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
  function mapelSelect(initialCount) {
    return {
      dipilih: initialCount,

      hitung() {
        this.dipilih = document.querySelectorAll('input[name="daftar_mapel[]"]:checked').length;
      },

      pilihSemua() {
        document.querySelectorAll('input[name="daftar_mapel[]"]').forEach(cb => cb.checked = true);
        this.hitung();
      },

      resetSemua() {
        document.querySelectorAll('input[name="daftar_mapel[]"]').forEach(cb => cb.checked = false);
        this.hitung();
      }
    }
  }
</script>
@endpush
@endsection