{{-- resources/views/admin/pengumuman/_form.blade.php --}}
@csrf

<div class="space-y-5">
    {{-- Judul --}}
    <div>
        <label for="judul" class="mb-1 block text-sm font-semibold text-gray-700">
            Judul Pengumuman
            <span class="text-red-500">*</span>
        </label>

        <input
            id="judul"
            type="text"
            name="judul"
            value="{{ old('judul', $item->judul ?? '') }}"
            required
            maxlength="180"
            class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Contoh: Jadwal Ujian Tengah Semester"
        >

        @error('judul')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kategori --}}
    <div>
        <label for="kategori" class="mb-1 block text-sm font-semibold text-gray-700">
            Kategori
        </label>

        <select
            id="kategori"
            name="kategori"
            class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="">Pilih kategori pengumuman</option>

            @foreach($kategoriOptions as $val => $label)
                <option value="{{ $val }}" @selected((string) old('kategori', $item->kategori ?? '') === (string) $val)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('kategori')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Isi --}}
    <div>
        <label for="isi" class="mb-1 block text-sm font-semibold text-gray-700">
            Isi Pengumuman
            <span class="text-red-500">*</span>
        </label>

        <textarea
            id="isi"
            name="isi"
            rows="9"
            required
            class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Tulis isi pengumuman secara jelas dan mudah dipahami..."
        >{{ old('isi', $item->isi ?? '') }}</textarea>

        @error('isi')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tombol --}}
    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin.pengumuman.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Batal
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            Simpan Draft
        </button>
    </div>
</div>