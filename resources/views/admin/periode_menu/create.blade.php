@extends('layouts.admin')
@section('title','Buat Periode Pemilihan Menu XI')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Buat Periode Pemilihan Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Buat periode pemilihan menu rombel untuk tahun ajaran tujuan.
      </p>
    </div>

    <a href="{{ route('admin.periode.index') }}"
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

  <form method="POST" action="{{ route('admin.periode.store') }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <h2 class="text-base font-semibold text-gray-800">Informasi Periode</h2>
        <p class="mt-1 text-sm text-gray-500">
          Pilih tahun ajaran tujuan dan tentukan rentang waktu pemilihan.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Tahun Ajaran Tujuan
          </label>
          <select
            name="tahun_ajaran_id"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('tahun_ajaran_id') border-red-400 @enderror"
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
            <p class="mt-1 text-xs text-gray-500">
              Pilih TA tujuan, biasanya TA baru yang masih nonaktif.
            </p>
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Nama Periode
          </label>
          <input
            type="text"
            name="nama_periode"
            value="{{ old('nama_periode') }}"
            placeholder="Contoh: Periode Pemilihan Menu Rombel"
            maxlength="150"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('nama_periode') border-red-400 @enderror"
            required>
          @error('nama_periode')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Tanggal Mulai
          </label>
          <input
            type="datetime-local"
            name="tanggal_mulai"
            value="{{ old('tanggal_mulai') }}"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('tanggal_mulai') border-red-400 @enderror"
            required>
          @error('tanggal_mulai')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-700">
            Tanggal Selesai
          </label>
          <input
            type="datetime-local"
            name="tanggal_selesai"
            value="{{ old('tanggal_selesai') }}"
            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('tanggal_selesai') border-red-400 @enderror"
            required>
          @error('tanggal_selesai')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <a href="{{ route('admin.periode.index') }}"
         class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Batal
      </a>

      <button
        type="submit"
        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
        Simpan Periode
      </button>
    </div>
  </form>
</div>
@endsection