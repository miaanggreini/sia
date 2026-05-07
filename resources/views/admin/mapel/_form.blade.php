@php
  $formAction = $formAction ?? route('admin.mapel.update', $mapel);
  $formMethod = $formMethod ?? 'PUT';
  $backUrl = $backUrl ?? route('admin.mapel.index');
@endphp

<form action="{{ $formAction }}" method="POST" class="space-y-6">
  @csrf

  @if(strtoupper($formMethod) !== 'POST')
    @method($formMethod)
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    {{-- Nama Mapel --}}
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Nama Mapel <span class="text-red-500">*</span>
      </label>

      <input
        type="text"
        name="nama_mapel"
        value="{{ old('nama_mapel', $mapel->nama_mapel) }}"
        required
        class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
      >

      @error('nama_mapel')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Kelompok --}}
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Kelompok
      </label>

      @php $kel = old('kelompok', $mapel->kelompok); @endphp

      <select
        name="kelompok"
        class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
      >
        <option value="">—</option>
        <option value="Umum" @selected($kel === 'Umum')>Umum</option>
        <option value="Pilihan" @selected($kel === 'Pilihan')>Pilihan</option>
      </select>

      @error('kelompok')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- KKM --}}
    <div>
      <label class="block text-sm font-medium text-gray-700">
        KKM
      </label>

      <input
        type="number"
        name="kkm"
        min="0"
        max="100"
        value="{{ old('kkm', $mapel->kkm) }}"
        class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
      >

      @error('kkm')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Status --}}
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Status
      </label>

      @php $s = old('status', $mapel->status ?? 'aktif'); @endphp

      <select
        name="status"
        class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
      >
        <option value="aktif" @selected($s === 'aktif')>Aktif</option>
        <option value="nonaktif" @selected($s === 'nonaktif')>Nonaktif</option>
      </select>

      @error('status')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>
  </div>

  <div class="pt-4 border-t flex items-center justify-start gap-3">
    <a
      href="{{ $backUrl }}"
      class="px-4 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-50"
    >
      Batal
    </a>

    <button
      type="submit"
      class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700"
    >
      Simpan
    </button>
  </div>
</form>