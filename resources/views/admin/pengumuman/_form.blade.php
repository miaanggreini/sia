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

    {{-- Tanggal Publikasi --}}
    <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4">
        <div class="mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm font-semibold text-blue-800">Jadwal Publikasi</span>
        </div>

        <p class="mb-4 text-xs text-blue-600">
            Tentukan kapan pengumuman ini akan tampil dan berakhir di halaman siswa. Tanggal ini akan dilihat Kepala Sekolah saat melakukan persetujuan.
        </p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Tanggal Mulai --}}
            <div>
                <label for="tanggal_mulai" class="mb-1 block text-sm font-semibold text-gray-700">
                    Tanggal Mulai Tayang
                    <span class="text-red-500">*</span>
                </label>

                <input
                    id="tanggal_mulai"
                    type="date"
                    name="tanggal_mulai"
                    value="{{ old('tanggal_mulai', isset($item->tanggal_mulai) ? $item->tanggal_mulai->format('Y-m-d') : '') }}"
                    required
                    class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                @error('tanggal_mulai')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Selesai --}}
            <div>
                <label for="tanggal_selesai" class="mb-1 block text-sm font-semibold text-gray-700">
                    Tanggal Selesai Tayang
                    <span class="text-xs font-normal text-gray-400">(opsional)</span>
                </label>

                <input
                    id="tanggal_selesai"
                    type="date"
                    name="tanggal_selesai"
                    value="{{ old('tanggal_selesai', isset($item->tanggal_selesai) ? $item->tanggal_selesai->format('Y-m-d') : '') }}"
                    class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak ada batas akhir penayangan.</p>

                @error('tanggal_selesai')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
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