{{-- resources/views/admin/pengumuman/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
@php
    $statusLabel = function ($status) {
        return match ($status) {
            'draft'    => 'Draft',
            'pending'  => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'publik'   => 'Dipublikasikan',
            default    => ucfirst((string) $status),
        };
    };

    $statusClass = function ($status) {
        return match ($status) {
            'draft'    => 'bg-gray-100 text-gray-700 border-gray-200',
            'pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
            'publik'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            default    => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    };

    $kategoriLabel = function ($kategori) use ($kategoriOptions) {
        return $kategoriOptions[$kategori] ?? ($kategori ? ucfirst($kategori) : 'Tanpa Kategori');
    };

    $kategoriClass = function ($kategori) {
        return match ($kategori) {
            'akademik'        => 'bg-blue-50 text-blue-700 border-blue-200',
            'kesiswaan'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ekstrakurikuler' => 'bg-orange-50 text-orange-700 border-orange-200',
            'kegiatan'        => 'bg-purple-50 text-purple-700 border-purple-200',
            default           => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    };

    $formatTanggal = function ($tanggal) {
        if (empty($tanggal)) {
            return '-';
        }

        return \Illuminate\Support\Carbon::parse($tanggal)->format('d M Y');
    };

    $statusPublikasi = function ($item) {
        if ($item->status !== 'approved') {
            return null;
        }

        if (empty($item->tanggal_mulai)) {
            return [
                'label' => 'Belum Ada Jadwal',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ];
        }

        $today = now('Asia/Jakarta')->startOfDay();
        $tanggalMulai = \Illuminate\Support\Carbon::parse($item->tanggal_mulai)->startOfDay();
        $tanggalSelesai = !empty($item->tanggal_selesai)
            ? \Illuminate\Support\Carbon::parse($item->tanggal_selesai)->endOfDay()
            : null;

        if ($tanggalMulai->gt($today)) {
            return [
                'label' => 'Terjadwal',
                'class' => 'bg-blue-100 text-blue-700 border-blue-200',
            ];
        }

        if ($tanggalSelesai && $tanggalSelesai->lt($today)) {
            return [
                'label' => 'Berakhir',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ];
        }

        return [
            'label' => 'Sedang Tampil',
            'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        ];
    };
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">
                Pengumuman
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola pengumuman sekolah, ajukan persetujuan ke Kepala Sekolah, dan tampilkan otomatis ke siswa sesuai jadwal publikasi.
            </p>
        </div>

        <a href="{{ route('admin.pengumuman.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <span class="text-lg leading-none">+</span>
            Tambah Pengumuman
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.pengumuman.index') }}">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <label for="q" class="mb-1 block text-sm font-semibold text-gray-700">
                        Cari Pengumuman
                    </label>

                    <input type="text"
                           id="q"
                           name="q"
                           value="{{ $q }}"
                           class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Cari berdasarkan judul atau isi pengumuman...">
                </div>

                <div class="lg:col-span-4">
                    <label for="kategori" class="mb-1 block text-sm font-semibold text-gray-700">
                        Kategori
                    </label>

                    <select id="kategori"
                            name="kategori"
                            class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>

                        @foreach($kategoriOptions as $val => $label)
                            <option value="{{ $val }}" @selected((string) $kategori === (string) $val)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Terapkan
                    </button>
                </div>
            </div>

            @if($q || $kategori)
                <div class="mt-3">
                    <a href="{{ route('admin.pengumuman.index') }}"
                       class="text-xs font-semibold text-gray-500 hover:text-blue-600">
                        Reset filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Ringkasan kecil --}}
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
            Perlu diajukan:
            <span class="rounded-full bg-white px-2 py-0.5 text-amber-800">
                {{ $perluDiajukanCount ?? 0 }}
            </span>
        </span>

        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
            Menunggu persetujuan:
            <span class="rounded-full bg-white px-2 py-0.5 text-blue-800">
                {{ $menungguApprovalCount ?? 0 }}
            </span>
        </span>

        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
            Disetujui:
            <span class="rounded-full bg-white px-2 py-0.5 text-emerald-800">
                {{ $siapPublikasiCount ?? 0 }}
            </span>
        </span>
    </div>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200">
                        <th class="w-16 px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            No
                        </th>

                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Judul
                        </th>

                        <th class="w-44 px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Kategori
                        </th>

                        <th class="w-56 px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Jadwal Publikasi
                        </th>

                        <th class="w-48 px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Status
                        </th>

                        <th class="w-56 px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $item)
                        @php
                            $publikasi = $statusPublikasi($item);
                        @endphp

                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-600">
                                {{ $items->firstItem() + $loop->index }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold leading-relaxed text-gray-900">
                                    {{ $item->judul }}
                                </div>

                                @if(!empty($item->alasan_tolak) && $item->status === 'rejected')
                                    <div class="mt-1 text-xs text-rose-600">
                                        Alasan ditolak: {{ $item->alasan_tolak }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $kategoriClass($item->kategori) }}">
                                    {{ $kategoriLabel($item->kategori) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-xs font-semibold text-gray-700">
                                    {{ $formatTanggal($item->tanggal_mulai) }}
                                    @if(!empty($item->tanggal_selesai))
                                        <span class="text-gray-400">s.d.</span>
                                        {{ $formatTanggal($item->tanggal_selesai) }}
                                    @else
                                        <span class="text-gray-400">s.d. tanpa batas</span>
                                    @endif
                                </div>

                                @if($publikasi)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $publikasi['class'] }}">
                                            {{ $publikasi['label'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass($item->status) }}">
                                    {{ $statusLabel($item->status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-nowrap items-center gap-2 whitespace-nowrap">
                                    @if(in_array($item->status, ['draft', 'rejected'], true))
                                        <a href="{{ route('admin.pengumuman.edit', $item) }}"
                                           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                            Ubah
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.pengumuman.submit', $item) }}"
                                              class="inline form-ajukan-pengumuman">
                                            @csrf
                                            <button type="button"
                                                    class="btn-ajukan-pengumuman inline-flex items-center justify-center rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                                                    data-title="{{ $item->judul }}"
                                                    data-jadwal="{{ $formatTanggal($item->tanggal_mulai) }}{{ !empty($item->tanggal_selesai) ? ' s.d. '.$formatTanggal($item->tanggal_selesai) : ' s.d. tanpa batas' }}"
                                                    data-action="{{ $item->status === 'rejected' ? 'Ajukan Ulang' : 'Ajukan' }}">
                                                {{ $item->status === 'rejected' ? 'Ajukan Ulang' : 'Ajukan' }}
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.pengumuman.show', $item) }}"
                                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="text-sm font-semibold text-gray-800">
                                        Belum ada pengumuman
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Pengumuman akan tampil di sini setelah admin membuat data pengumuman.
                                    </p>

                                    <a href="{{ route('admin.pengumuman.create') }}"
                                       class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        Tambah Pengumuman
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Konfirmasi Ajukan --}}
<div id="modalAjukanPengumuman"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-6 w-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </div>

            <div class="min-w-0">
                <h3 class="text-lg font-bold text-gray-900">
                    Ajukan Pengumuman?
                </h3>

                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    Pengumuman ini akan diajukan ke Kepala Sekolah untuk proses persetujuan. Kepala Sekolah akan meninjau isi pengumuman dan jadwal publikasinya.
                </p>

                <div class="mt-3 rounded-xl bg-gray-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Judul Pengumuman
                    </p>
                    <p id="modalAjukanTitle" class="mt-1 text-sm font-semibold text-gray-900">
                        -
                    </p>

                    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-500">
                        Jadwal Publikasi
                    </p>
                    <p id="modalAjukanJadwal" class="mt-1 text-sm font-semibold text-gray-900">
                        -
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button"
                    id="btnBatalAjukan"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Batal
            </button>

            <button type="button"
                    id="btnKonfirmasiAjukan"
                    class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                Ajukan
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalAjukanPengumuman');
        const modalTitle = document.getElementById('modalAjukanTitle');
        const modalJadwal = document.getElementById('modalAjukanJadwal');
        const btnBatal = document.getElementById('btnBatalAjukan');
        const btnKonfirmasi = document.getElementById('btnKonfirmasiAjukan');

        let selectedForm = null;

        document.querySelectorAll('.btn-ajukan-pengumuman').forEach((button) => {
            button.addEventListener('click', function () {
                selectedForm = this.closest('form');

                modalTitle.textContent = this.dataset.title || '-';
                modalJadwal.textContent = this.dataset.jadwal || '-';
                btnKonfirmasi.textContent = this.dataset.action || 'Ajukan';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            selectedForm = null;
        }

        btnBatal.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        btnKonfirmasi.addEventListener('click', function () {
            if (selectedForm) {
                selectedForm.submit();
            }
        });
    });
</script>
@endsection