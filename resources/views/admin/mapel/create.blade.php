@extends('layouts.admin')

@section('content')
  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Tambah Mata Pelajaran</h1>
      <p class="text-gray-600 mt-1">Lengkapi data mata pelajaran dengan benar.</p>
    </div>

    <a href="{{ route('admin.mapel.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border-2 border-indigo-500 text-indigo-600 hover:bg-indigo-50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
      </svg>
      Kembali
    </a>
  </div>

  {{-- Notifikasi Error --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 p-3">
      <div class="font-medium mb-1">Periksa kembali isian berikut:</div>
      <ul class="list-disc ml-5 text-sm">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Card Form --}}
  <div class="bg-white rounded-2xl shadow p-6">
    <form method="POST" action="{{ route('admin.mapel.store') }}" class="space-y-6">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Nama Mapel --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Nama Mapel <span class="text-red-500">*</span>
          </label>
          <input type="text" name="nama_mapel" value="{{ old('nama_mapel') }}" required
                 class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
          @error('nama_mapel')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Kelompok --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Kelompok</label>
          @php $kel = old('kelompok'); @endphp
          <select name="kelompok"
                  class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Pilih Kelompok —</option>
            <option value="Umum" @selected($kel === 'Umum')>Umum</option>
            <option value="Pilihan" @selected($kel === 'Pilihan')>Pilihan</option>
          </select>
          @error('kelompok')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- KKM --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">KKM</label>
          <input type="number" name="kkm" value="{{ old('kkm') }}" min="0" max="100"
                 class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
          @error('kkm')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Status --}}
        <div>
          <label class="block text-sm font-medium text-gray-700">Status</label>
          @php $s = old('status', 'aktif'); @endphp
          <select name="status"
                  class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="aktif" @selected($s === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected($s === 'nonaktif')>Nonaktif</option>
          </select>
          @error('status')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Footer Actions --}}
      <div class="pt-4 border-t flex items-center gap-3">
        <a href="{{ route('admin.mapel.index') }}"
           class="px-4 py-2 rounded-md border hover:bg-gray-50">Batal</a>
        <button type="submit"
                class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
          Simpan
        </button>
      </div>
    </form>
  </div>
@endsection