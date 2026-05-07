{{-- resources/views/admin/menu_rombel/create.blade.php --}}
@extends('layouts.admin')
@section('title','Buat Menu Rombel')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Buat Menu Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Tambahkan menu pilihan rombel kelas XI berdasarkan mata pelajaran pilihan.
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
      <ul class="mt-2 list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.menu-rombel.store') }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <h2 class="text-base font-semibold text-gray-800">Informasi Menu</h2>
        <p class="mt-1 text-sm text-gray-500">
          Tentukan tahun ajaran tujuan, nama menu, dan kapasitas total menu rombel.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Tahun Ajaran Tujuan
          </label>

          <select
            name="tahun_ajaran_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('tahun_ajaran_id') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>
            <option value="">— Pilih Tahun Ajaran —</option>
            @foreach($tahunAjaran as $ta)
              <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id') == $ta->id)>
                {{ $ta->nama_tahun ?? $ta->label ?? '-' }} — {{ ucfirst($ta->status ?? 'nonaktif') }}
              </option>
            @endforeach
          </select>

          @error('tahun_ajaran_id')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Nama Menu
          </label>

          <input
            type="text"
            name="nama"
            value="{{ old('nama') }}"
            placeholder="Contoh: Menu Saintek"
            maxlength="100"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('nama') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>

          @error('nama')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
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
            step="1"
            inputmode="numeric"
            value="{{ old('kapasitas_total') }}"
            placeholder="Contoh: 50"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('kapasitas_total') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            required>

          @error('kapasitas_total')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @else
          @enderror
        </div>
      </div>
    </div>

    <div class="rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
          <div>
            <h2 class="text-base font-semibold text-gray-800">Mata Pelajaran Pilihan</h2>
            <p class="mt-1 text-sm text-gray-500">
              Pilih mata pelajaran pilihan yang termasuk dalam menu rombel ini.
            </p>
          </div>

          <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
            Khusus mapel pilihan
          </span>
        </div>
      </div>

      <div class="p-5">
        @if($daftarMapel->isEmpty())
          <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
            <div class="text-sm font-semibold text-gray-700">
              Belum ada data mapel pilihan.
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Pastikan data mata pelajaran berikut sudah tersedia di data master:
              Matematika Tingkat Lanjut, Fisika, Kimia, Biologi, Sosiologi,
              Geografi, Ekonomi, Informatika, dan Koding.
            </p>
          </div>
        @else

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($daftarMapel as $m)
              <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                <input
                  type="checkbox"
                  name="daftar_mapel[]"
                  value="{{ $m->id }}"
                  @checked(in_array($m->id, old('daftar_mapel', [])))
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                <span class="font-medium text-gray-700 group-hover:text-blue-700">
                  {{ $m->nama_mapel }}
                </span>
              </label>
            @endforeach
          </div>

          @error('daftar_mapel')
            <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
          @enderror

          @error('daftar_mapel.*')
            <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
          @enderror
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
        Simpan Menu
      </button>
    </div>
  </form>
</div>
@endsection