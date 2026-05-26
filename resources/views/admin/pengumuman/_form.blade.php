{{-- resources/views/admin/pengumuman/_form.blade.php --}}

@php
    $item = $item ?? new \App\Models\Pengumuman();

    $kategoriOptions = $kategoriOptions ?? [
        'akademik' => 'Akademik',
        'kesiswaan' => 'Kesiswaan',
        'ekstrakurikuler' => 'Ekstrakurikuler',
        'kegiatan' => 'Kegiatan',
    ];

    $todayValue = now('Asia/Jakarta')->toDateString();

    $tanggalMulaiValue = old(
        'tanggal_mulai',
        !empty($item->tanggal_mulai)
            ? \Illuminate\Support\Carbon::parse($item->tanggal_mulai)->format('Y-m-d')
            : $todayValue
    );

    $tanggalSelesaiValue = old(
        'tanggal_selesai',
        !empty($item->tanggal_selesai)
            ? \Illuminate\Support\Carbon::parse($item->tanggal_selesai)->format('Y-m-d')
            : ''
    );
@endphp

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
            <span class="text-red-500">*</span>
        </label>

        <select
            id="kategori"
            name="kategori"
            required
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

    {{-- Jadwal Publikasi --}}
    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
        <div class="mb-3">
            <h3 class="text-sm font-bold text-gray-800">
                Jadwal Publikasi
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                Tanggal mulai otomatis terisi hari ini. Tanggal sebelum hari ini tidak dapat dipilih.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Tanggal Mulai --}}
            <div>
                <label for="tanggal_mulai" class="mb-1 block text-sm font-semibold text-gray-700">
                    Tanggal Mulai Publikasi
                    <span class="text-red-500">*</span>
                </label>

                <input
                    id="tanggal_mulai"
                    type="date"
                    name="tanggal_mulai"
                    value="{{ $tanggalMulaiValue }}"
                    min="{{ $todayValue }}"
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
                    Tanggal Selesai Publikasi
                </label>

                <input
                    id="tanggal_selesai"
                    type="date"
                    name="tanggal_selesai"
                    value="{{ $tanggalSelesaiValue }}"
                    min="{{ $tanggalMulaiValue }}"
                    class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Boleh dikosongkan jika pengumuman tidak memiliki batas akhir publikasi.
                </p>

                @error('tanggal_selesai')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
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
            {{ $item->exists ? 'Simpan Perubahan' : 'Simpan Draft' }}
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalSelesai = document.getElementById('tanggal_selesai');

        if (!tanggalMulai || !tanggalSelesai) return;

        const today = tanggalMulai.getAttribute('min');

        tanggalMulai.addEventListener('change', function () {
            if (tanggalMulai.value && tanggalMulai.value < today) {
                tanggalMulai.value = today;
            }

            tanggalSelesai.min = tanggalMulai.value || today;

            if (tanggalSelesai.value && tanggalSelesai.value < tanggalSelesai.min) {
                tanggalSelesai.value = '';
            }
        });

        tanggalSelesai.addEventListener('change', function () {
            const minSelesai = tanggalMulai.value || today;

            if (tanggalSelesai.value && tanggalSelesai.value < minSelesai) {
                tanggalSelesai.value = '';
            }
        });
    });
</script>