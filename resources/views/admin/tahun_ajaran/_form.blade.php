@php
  use Carbon\Carbon;

  $start = 2024;
  $end = date('Y') + 2;

  $selectedTahun = old('nama_tahun', $item->nama_tahun ?? '');
  $selectedSemester = old('semester', $item->semester ?? 'Ganjil');
  $selectedStatus = old('status', $item->status ?? 'nonaktif');

  $tanggalMulai = old(
      'tanggal_mulai',
      !empty($item->tanggal_mulai) ? Carbon::parse($item->tanggal_mulai)->format('Y-m-d') : ''
  );

  $tanggalSelesai = old(
      'tanggal_selesai',
      !empty($item->tanggal_selesai) ? Carbon::parse($item->tanggal_selesai)->format('Y-m-d') : ''
  );
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- Tahun Ajaran --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">
      Tahun Ajaran <span class="text-red-500">*</span>
    </label>

    <select name="nama_tahun"
            required
            class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <option value="">— Pilih Tahun Ajaran —</option>
      @for ($y = $end; $y >= $start; $y--)
        @php $tahun = $y . '/' . ($y + 1); @endphp
        <option value="{{ $tahun }}" @selected($selectedTahun === $tahun)>
          {{ $tahun }}
        </option>
      @endfor
    </select>

    @error('nama_tahun')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

  </div>

  {{-- Semester --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">
      Semester Aktif
    </label>

    <select name="semester"
            class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <option value="Ganjil" @selected($selectedSemester === 'Ganjil')>Ganjil</option>
      <option value="Genap" @selected($selectedSemester === 'Genap')>Genap</option>
    </select>

    @error('semester')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

  </div>

  {{-- Tanggal Mulai --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
    <input type="date"
           name="tanggal_mulai"
           value="{{ $tanggalMulai }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

    @error('tanggal_mulai')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- Tanggal Selesai --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
    <input type="date"
           name="tanggal_selesai"
           value="{{ $tanggalSelesai }}"
           class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

    @error('tanggal_selesai')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- Status --}}
  <div>
    <label class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status"
            class="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <option value="aktif" @selected($selectedStatus === 'aktif')>Aktif</option>
      <option value="nonaktif" @selected($selectedStatus === 'nonaktif')>Nonaktif</option>
    </select>

    @error('status')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>
</div>