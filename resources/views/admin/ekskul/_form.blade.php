@php
    $isKepsek = auth()->user()?->role === 'kepala_sekolah';
    $cancelRoute = $isKepsek
        ? route('kepala_sekolah.data.ekskul')
        : route('admin.ekskul.index');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div>
        <label for="nama" class="block text-sm font-medium text-gray-700">
            Nama Ekskul <span class="text-rose-600">*</span>
        </label>
        <input type="text" name="nama" id="nama"
            value="{{ old('nama', $ekskul->nama ?? '') }}" required
            placeholder="Contoh: Futsal"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('nama') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="pembina_id" class="block text-sm font-medium text-gray-700">
            Pembina <span class="text-rose-600">*</span>
        </label>
        <select name="pembina_id" id="pembina_id"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
            <option value="">— Pilih Guru —</option>
            @foreach($daftarGuru as $g)
                <option value="{{ $g->id }}"
                    @selected(old('pembina_id', $ekskul->pembina_id ?? null) == $g->id)>
                    {{ $g->nama }}
                </option>
            @endforeach
        </select>
        @error('pembina_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="hari" class="block text-sm font-medium text-gray-700">Hari</label>
        @php $hari = old('hari', $ekskul->hari ?? ''); @endphp
        <select name="hari" id="hari"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Pilih Hari —</option>
            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h)
                <option value="{{ $h }}" @selected($hari === $h)>{{ $h }}</option>
            @endforeach
        </select>
        @error('hari') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="jam_mulai" class="block text-sm font-medium text-gray-700">Jam Mulai</label>
        <input type="time" name="jam_mulai" id="jam_mulai"
            value="{{ old('jam_mulai', isset($ekskul->jam_mulai) ? substr($ekskul->jam_mulai, 0, 5) : '') }}"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('jam_mulai') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="jam_selesai" class="block text-sm font-medium text-gray-700">Jam Selesai</label>
        <input type="time" name="jam_selesai" id="jam_selesai"
            value="{{ old('jam_selesai', isset($ekskul->jam_selesai) ? substr($ekskul->jam_selesai, 0, 5) : '') }}"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('jam_selesai') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6">
    <label for="lokasi" class="block text-sm font-medium text-gray-700">Lokasi</label>
    <input type="text" name="lokasi" id="lokasi"
        value="{{ old('lokasi', $ekskul->lokasi ?? '') }}"
        placeholder="Contoh: Lapangan Sekolah atau Ruang Musik"
        class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    @error('lokasi') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div class="pt-6 flex gap-3 justify-end border-t mt-8">
    <a href="{{ $cancelRoute }}"
       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition shadow-sm">
        Batal
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition shadow-md">
        Simpan
    </button>
</div>