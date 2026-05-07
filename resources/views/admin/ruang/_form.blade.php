@csrf
@php
  $backRoute = auth()->user()?->role === 'kepala_sekolah'
      ? route('kepala_sekolah.data.ruang-kelas')
      : route('admin.ruang-kelas.index');
@endphp

<div class="grid sm:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm text-gray-600 mb-1">Nama Ruang <span class="text-red-600">*</span></label>
    <input type="text" name="nama" value="{{ old('nama', $item->nama) }}" required
           class="w-full px-3 py-2 border rounded-md" placeholder="mis. R. XI-1 / Lab Fisika">
    @error('nama')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
  </div>
  <div>
    <label class="block text-sm text-gray-600 mb-1">Kapasitas</label>
    <input type="number" name="kapasitas" value="{{ old('kapasitas', $item->kapasitas) }}"
           class="w-full px-3 py-2 border rounded-md" placeholder="mis. 25" min="1" max="200">
    @error('kapasitas')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
  </div>
</div>

<div class="mt-6 flex items-center gap-2">
  <a href="{{ $backRoute }}" class="px-4 py-2 rounded-md border bg-white hover:bg-gray-50">Kembali</a>
  <button class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
    Simpan
  </button>
</div>