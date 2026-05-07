{{-- resources/views/admin/pengumuman/publish.blade.php --}}
@extends('layouts.admin')

@section('content')
  <h1 class="text-2xl font-semibold mb-4">Atur Jadwal Publikasi</h1>

  <div class="bg-white rounded-lg shadow p-4">
    <div class="mb-4">
      <div class="text-sm text-gray-500">Judul</div>
      <div class="font-medium">{{ $item->judul }}</div>
    </div>

    <form method="POST" action="{{ route('admin.pengumuman.publish.store', $item) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf

      <div>
        <label class="block text-sm mb-1">Tanggal Mulai <span class="text-red-600">*</span></label>
        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required class="w-full border-gray-300 rounded-md">
        @error('tanggal_mulai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Tanggal Selesai (opsional)</label>
        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full border-gray-300 rounded-md">
        @error('tanggal_selesai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="md:col-span-2 pt-2 flex gap-2">
        <a href="{{ route('admin.pengumuman.index') }}" class="px-4 py-2 border rounded-md hover:bg-gray-50">Batal</a>
        <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Publikasikan</button>
      </div>
    </form>

    <p class="text-xs text-gray-500 mt-4">
      Catatan: Tanggal selesai boleh dikosongkan jika tidak ada batas akhir penayangan.
    </p>
  </div>
@endsection
